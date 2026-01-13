<?php
// app/Http/Controllers/SiswaController.php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    /**
     * Display a listing of materi.
     */
    public function index(Request $request)
    {
        $siswa = auth()->user();
        $kelas = $siswa->kelas;
        
        $query = Materi::with(['guru'])
            ->where('kelas', $kelas)
            ->where('is_published', true)
            ->where('tanggal_mulai', '<=', Carbon::now());

        // Filter by tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $materi = $query->orderBy('tanggal_mulai', 'desc')->paginate(12);

        // Statistics
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

        return view('siswa.materi.show', compact('materi', 'stats'));
    }

    /**
     * Display the specified materi.
     */
    public function show(Materi $materi)
    {
        // Check authorization
        if ($materi->kelas !== auth()->user()->kelas) {
            abort(403, 'Anda tidak memiliki akses ke materi ini');
        }

        if (!$materi->is_published) {
            abort(404, 'Materi tidak tersedia');
        }

        if (Carbon::now()->lt($materi->tanggal_mulai)) {
            return back()->with('error', 'Materi belum dapat diakses');
        }

        $materi->load('guru');

        // Record or update absensi
        $this->recordAbsensi($materi);

        // Get jawaban kuis if exists
        $jawabanKuis = null;
        if ($materi->tipe === 'kuis') {
            $jawabanKuis = JawabanKuis::where('materi_id', $materi->id)
                ->where('siswa_id', auth()->id())
                ->first();
        }

        // Check if already accessed
        $absensi = Absensi::where('materi_id', $materi->id)
            ->where('siswa_id', auth()->id())
            ->first();

        return view('siswa.materi.show', compact('materi', 'jawabanKuis', 'absensi'));
    }

    /**
     * Record absensi when siswa access materi.
     */
    private function recordAbsensi(Materi $materi)
    {
        $absensi = Absensi::firstOrNew([
            'materi_id' => $materi->id,
            'siswa_id' => auth()->id(),
        ]);

        // If new record
        if (!$absensi->exists) {
            $absensi->status = 'hadir';
            $absensi->waktu_akses = now();
            $absensi->ip_address = request()->ip();
            $absensi->user_agent = request()->userAgent();
            $absensi->save();
        } else {
            // Update waktu_akses and calculate durasi
            if ($absensi->waktu_akses) {
                $durasi = $absensi->waktu_akses->diffInMinutes(now());
                $absensi->durasi_akses = ($absensi->durasi_akses ?? 0) + $durasi;
            }
            $absensi->waktu_akses = now();
            $absensi->save();
        }
    }

    /**
     * Submit jawaban kuis.
     */
    public function submitKuis(Request $request, Materi $materi)
    {
        // Validasi
        if ($materi->tipe !== 'kuis') {
            return back()->with('error', 'Ini bukan materi kuis');
        }

        if ($materi->kelas !== auth()->user()->kelas) {
            abort(403);
        }

        $request->validate([
            'jawaban' => ['required', 'string', 'min:10'],
        ], [
            'jawaban.required' => 'Jawaban wajib diisi',
            'jawaban.min' => 'Jawaban minimal 10 karakter',
        ]);

        // Check if deadline passed
        if ($materi->tanggal_selesai && now()->gt($materi->tanggal_selesai)) {
            return back()->with('error', 'Waktu pengumpulan kuis telah berakhir');
        }

        DB::beginTransaction();
        
        try {
            // Save or update jawaban
            JawabanKuis::updateOrCreate(
                [
                    'materi_id' => $materi->id,
                    'siswa_id' => auth()->id(),
                ],
                [
                    'jawaban' => $request->jawaban,
                ]
            );

            DB::commit();

            return back()->with('success', 'Jawaban kuis berhasil dikirim');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Display riwayat absensi siswa.
     */
    public function riwayatAbsensi(Request $request)
    {
        $query = Absensi::with(['materi.guru'])
            ->where('siswa_id', auth()->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('waktu_akses', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $absensi = $query->orderBy('waktu_akses', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Absensi::where('siswa_id', auth()->id())->count(),
            'hadir' => Absensi::where('siswa_id', auth()->id())->where('status', 'hadir')->count(),
            'tidak_hadir' => Absensi::where('siswa_id', auth()->id())->where('status', 'tidak_hadir')->count(),
            'sakit' => Absensi::where('siswa_id', auth()->id())->where('status', 'sakit')->count(),
            'izin' => Absensi::where('siswa_id', auth()->id())->where('status', 'izin')->count(),
        ];

        return view('siswa.riwayat-absensi', compact('absensi', 'stats'));
    }

    /**
     * Display riwayat jawaban kuis.
     */
    public function riwayatKuis(Request $request)
    {
        $query = JawabanKuis::with(['materi.guru'])
            ->where('siswa_id', auth()->id());

        // Filter by sudah dinilai
        if ($request->filled('dinilai')) {
            if ($request->dinilai === 'sudah') {
                $query->whereNotNull('nilai');
            } else {
                $query->whereNull('nilai');
            }
        }

        $jawaban = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate statistics
        $stats = [
            'total_kuis' => JawabanKuis::where('siswa_id', auth()->id())->count(),
            'sudah_dinilai' => JawabanKuis::where('siswa_id', auth()->id())
                ->whereNotNull('nilai')
                ->count(),
            'belum_dinilai' => JawabanKuis::where('siswa_id', auth()->id())
                ->whereNull('nilai')
                ->count(),
            'rata_nilai' => JawabanKuis::where('siswa_id', auth()->id())
                ->whereNotNull('nilai')
                ->avg('nilai'),
            'nilai_tertinggi' => JawabanKuis::where('siswa_id', auth()->id())
                ->whereNotNull('nilai')
                ->max('nilai'),
            'nilai_terendah' => JawabanKuis::where('siswa_id', auth()->id())
                ->whereNotNull('nilai')
                ->min('nilai'),
        ];

        return view('siswa.riwayat-kuis', compact('jawaban', 'stats'));
    }
}