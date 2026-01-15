<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = auth()->user();
        $kelas = $siswa->kelas;

        $query = Materi::with(['guru'])
            ->where('kelas', $kelas)
            ->where('is_published', true)
            ->where('tanggal_mulai', '<=', Carbon::now());

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $materi = $query->orderBy('tanggal_mulai', 'desc')->paginate(12);

        $stats = [
            'total_materi' => Materi::where('kelas', $kelas)
                ->where('is_published', true)
                ->where('tipe', 'materi')
                ->count(),
            'total_kuis' => Materi::where('kelas', $kelas)
                ->where('is_published', true)
                ->where('tipe', 'kuis')
                ->count(),
            'materi_diakses' => Absensi::where('siswa_id', $siswa->id)
                ->where('status', 'hadir')
                ->count(),
            'kuis_dijawab' => JawabanKuis::where('siswa_id', $siswa->id)->count(),
        ];

        return view('siswa.materi.index', compact('materi', 'stats'));
    }

    /**
     * Display the specified materi.
     */
    public function show(Materi $materi)
    {
        // akses kelas
        if ($materi->kelas !== auth()->user()->kelas) {
            abort(403, 'Anda tidak memiliki akses ke materi ini');
        }

        if (!$materi->is_published) {
            abort(404, 'Materi tidak tersedia');
        }

        if (Carbon::now()->lt($materi->tanggal_mulai)) {
            return back()->with('error', 'Materi belum dapat diakses');
        }

        // load relasi
        $materi->load(['guru', 'soals']);

        // absensi
        $this->recordAbsensi($materi);

        // jawaban kuis siswa (kalau kuis)
        $jawabanKuis = null;
        if ($materi->tipe === 'kuis') {
            $jawabanKuis = JawabanKuis::where('materi_id', $materi->id)
                ->where('siswa_id', auth()->id())
                ->first();
        }

        $absensi = Absensi::where('materi_id', $materi->id)
            ->where('siswa_id', auth()->id())
            ->first();

        $soals = $materi->soals;

        return view('siswa.materi.show', compact('materi', 'jawabanKuis', 'absensi', 'soals'));
    }

    private function recordAbsensi(Materi $materi)
    {
        $absensi = Absensi::firstOrNew([
            'materi_id' => $materi->id,
            'siswa_id' => auth()->id(),
        ]);

        if (!$absensi->exists) {
            $absensi->status = 'hadir';
            $absensi->waktu_akses = now();
            $absensi->ip_address = request()->ip();
            $absensi->user_agent = request()->userAgent();
            $absensi->save();
        } else {
            if ($absensi->waktu_akses) {
                $durasi = $absensi->waktu_akses->diffInMinutes(now());
                $absensi->durasi_akses = ($absensi->durasi_akses ?? 0) + $durasi;
            }
            $absensi->waktu_akses = now();
            $absensi->save();
        }
    }

    /**
     * Submit jawaban kuis pilihan ganda di web.
     * - Terima jawaban[soal_id] = a/b/c/d
     * - Hitung nilai otomatis dari kunci_jawaban + bobot_nilai (jika ada)
     * - Simpan jawaban sebagai JSON di kolom jawaban
     */
    public function submitKuis(Request $request, Materi $materi)
    {
        if ($materi->tipe !== 'kuis') {
            return back()->with('error', 'Ini bukan materi kuis');
        }

        if ($materi->kelas !== auth()->user()->kelas) {
            abort(403);
        }

        if ($materi->tanggal_selesai && now()->gt($materi->tanggal_selesai)) {
            return back()->with('error', 'Waktu pengerjaan kuis telah berakhir');
        }

        // ambil soal untuk validasi + hitung nilai
        $soals = Soal::where('materi_id', $materi->id)->get();

        if ($soals->count() === 0) {
            return back()->with('error', 'Soal belum tersedia');
        }

        // validasi format input
        $request->validate([
            'jawaban' => ['required', 'array'],
        ], [
            'jawaban.required' => 'Semua soal wajib dijawab',
        ]);

        // pastikan semua soal terjawab
        foreach ($soals as $soal) {
            if (!isset($request->jawaban[$soal->id])) {
                return back()->with('error', 'Semua soal wajib dijawab.');
            }
        }

        DB::beginTransaction();
        try {
            // hitung nilai
            $totalBobot = 0;
            $skorBenar  = 0;

            foreach ($soals as $soal) {
                $bobot = (int) ($soal->bobot_nilai ?? 1);
                $totalBobot += $bobot;

                $jawab = $request->jawaban[$soal->id]; // a/b/c/d
                if ($jawab === $soal->kunci_jawaban) {
                    $skorBenar += $bobot;
                }
            }

            $nilai = $totalBobot > 0 ? round(($skorBenar / $totalBobot) * 100) : 0;

            // simpan ringkasan jawaban (JSON) + nilai
            JawabanKuis::updateOrCreate(
                [
                    'materi_id' => $materi->id,
                    'siswa_id'  => auth()->id(),
                ],
                [
                    'jawaban' => json_encode($request->jawaban),
                    'nilai'   => $nilai,
                ]
            );

            DB::commit();
            return back()->with('success', 'Jawaban berhasil dikirim. Nilai kamu: ' . $nilai);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim jawaban: ' . $e->getMessage());
        }
    }

    public function riwayatAbsensi(Request $request)
    {
        $query = Absensi::with(['materi.guru'])
            ->where('siswa_id', auth()->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_akses', [$request->start_date, $request->end_date]);
        }

        $absensi = $query->orderBy('waktu_akses', 'desc')->paginate(20);

        $stats = [
            'total' => Absensi::where('siswa_id', auth()->id())->count(),
            'hadir' => Absensi::where('siswa_id', auth()->id())->where('status', 'hadir')->count(),
            'tidak_hadir' => Absensi::where('siswa_id', auth()->id())->where('status', 'tidak_hadir')->count(),
            'sakit' => Absensi::where('siswa_id', auth()->id())->where('status', 'sakit')->count(),
            'izin' => Absensi::where('siswa_id', auth()->id())->where('status', 'izin')->count(),
        ];

        return view('siswa.riwayat-absensi', compact('absensi', 'stats'));
    }

    public function riwayatKuis(Request $request)
    {
        $query = JawabanKuis::with(['materi.guru'])
            ->where('siswa_id', auth()->id());

        if ($request->filled('dinilai')) {
            if ($request->dinilai === 'sudah') {
                $query->whereNotNull('nilai');
            } else {
                $query->whereNull('nilai');
            }
        }

        $jawaban = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_kuis' => JawabanKuis::where('siswa_id', auth()->id())->count(),
            'sudah_dinilai' => JawabanKuis::where('siswa_id', auth()->id())->whereNotNull('nilai')->count(),
            'belum_dinilai' => JawabanKuis::where('siswa_id', auth()->id())->whereNull('nilai')->count(),
            'rata_nilai' => JawabanKuis::where('siswa_id', auth()->id())->whereNotNull('nilai')->avg('nilai'),
            'nilai_tertinggi' => JawabanKuis::where('siswa_id', auth()->id())->whereNotNull('nilai')->max('nilai'),
            'nilai_terendah' => JawabanKuis::where('siswa_id', auth()->id())->whereNotNull('nilai')->min('nilai'),
        ];

        return view('siswa.riwayat-kuis', compact('jawaban', 'stats'));
    }
}
