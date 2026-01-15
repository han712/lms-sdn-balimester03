<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\JawabanKuis;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    /**
     * Daftar Kuis yang dibuat guru.
     */
    public function index()
    {
        $kuis = Materi::where('tipe', 'kuis')
            ->where('guru_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('guru.kuis.index', compact('kuis'));
    }

    public function create()
    {
        $kelasList = config('lms.daftar_kelas', []);
        return view('guru.kuis.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $kelasList = config('lms.daftar_kelas', []);

        // 1. Validasi Header Kuis
        $request->validate([
            'judul' => 'required|string|max:255',
            'kelas' => ['required', Rule::in($kelasList)],
            'keterangan' => 'nullable|string',

            // Validasi Array Soal
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.tipe' => 'required|in:pilihan_ganda,essay',
            'soal.*.kunci' => 'required', // kunci wajib (PG radio / Essay dari JS rename)
            'soal.*.gambar' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // 2. Simpan Header Kuis sebagai 'Materi'
            $materi = Materi::create([
                'guru_id' => auth()->id(),
                'judul' => $request->judul,
                'keterangan' => $request->keterangan,
                'kelas' => $request->kelas,   // <- sekarang isinya "1A", "1B", dst
                'tipe' => 'kuis',
                'is_published' => false,
                'tanggal_mulai' => now(),
                // kalau kolom kkm ada di materi table:
                'kkm' => $request->kkm ?? 70,
            ]);

            // 3. Looping Simpan Tiap Soal
            foreach ($request->soal as $index => $item) {

                $pathGambar = null;

                if ($request->hasFile("soal.{$index}.gambar")) {
                    $file = $request->file("soal.{$index}.gambar");
                    $pathGambar = $file->store('soal-images', 'public');
                }

                $opsi = null;
                if ($item['tipe'] === 'pilihan_ganda') {
                    $opsi = [
                        'a' => $item['opsi']['a'] ?? '',
                        'b' => $item['opsi']['b'] ?? '',
                        'c' => $item['opsi']['c'] ?? '',
                        'd' => $item['opsi']['d'] ?? '',
                    ];
                }

                Soal::create([
                    'materi_id' => $materi->id,
                    'tipe_soal' => $item['tipe'],
                    'pertanyaan' => $item['pertanyaan'],
                    'gambar' => $pathGambar,
                    'opsi_jawaban' => $opsi,
                    'kunci_jawaban' => $item['kunci'],
                    'bobot_nilai' => $item['bobot'] ?? 10,
                ]);
            }

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Kuis berhasil dibuat! Silahkan publish agar siswa bisa mengerjakan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat kuis: ' . $e->getMessage());
        }
    }

    public function hasil(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) abort(403);
        if ($materi->tipe !== 'kuis') abort(404);

        $materi->load(['jawabanKuis.siswa', 'jawabanKuis' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        $stats = [
            'total_submit' => $materi->jawabanKuis->count(),
            'rata_rata' => round($materi->jawabanKuis->avg('nilai') ?? 0, 1),
            'tertinggi' => $materi->jawabanKuis->max('nilai') ?? 0,
            'terendah' => $materi->jawabanKuis->min('nilai') ?? 0,
        ];

        return view('guru.kuis.hasil', compact('materi', 'stats'));
    }

    public function detailJawaban(JawabanKuis $jawaban)
    {
        $jawaban->load(['materi.soals', 'siswa']);

        if ($jawaban->materi->guru_id !== Auth::id()) {
            abort(403);
        }

        return view('guru.kuis.detail', compact('jawaban'));
    }

    public function nilaiJawaban(Request $request, JawabanKuis $jawaban)
    {
        $request->validate([
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string'
        ]);

        if ($jawaban->materi->guru_id !== Auth::id()) {
            abort(403);
        }

        $jawaban->update([
            'nilai' => $request->nilai,
            'catatan_guru' => $request->catatan_guru,
            'dinilai_pada' => now(),
            'dinilai_oleh' => Auth::id()
        ]);

        return redirect()->route('guru.kuis.hasil', $jawaban->materi_id)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    public function edit($id)
    {
        $kuis = Materi::with('soals')->findOrFail($id);

        if ($kuis->guru_id !== Auth::id()) abort(403);
        if ($kuis->tipe !== 'kuis') return redirect()->route('guru.materi.edit', $kuis->id);

        $kelasList = config('lms.daftar_kelas', []);
        return view('guru.kuis.edit', compact('kuis', 'kelasList'));
    }

    public function update(Request $request, $id)
    {
        $kuis = Materi::findOrFail($id);
        if ($kuis->guru_id !== Auth::id()) abort(403);

        $kelasList = config('lms.daftar_kelas', []);

        $request->validate([
            'judul' => 'required|string|max:255',
            'kelas' => ['required', Rule::in($kelasList)],
            'keterangan' => 'nullable|string',
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.tipe' => 'required|in:pilihan_ganda,essay',
            'soal.*.kunci' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $kuis->update([
                'judul' => $request->judul,
                'keterangan' => $request->keterangan,
                'kelas' => $request->kelas, // <- "1A", "1B", dst
                'tanggal_mulai' => now(),
                'kkm' => $request->kkm ?? ($kuis->kkm ?? 70),
            ]);

            $kuis->soals()->delete();

            foreach ($request->soal as $index => $item) {

                $pathGambar = null;
                if ($request->hasFile("soal.{$index}.gambar")) {
                    $pathGambar = $request->file("soal.{$index}.gambar")->store('soal-images', 'public');
                } elseif (isset($item['gambar_lama'])) {
                    $pathGambar = $item['gambar_lama'];
                }

                $opsi = null;
                if ($item['tipe'] === 'pilihan_ganda') {
                    $opsi = [
                        'a' => $item['opsi']['a'] ?? '',
                        'b' => $item['opsi']['b'] ?? '',
                        'c' => $item['opsi']['c'] ?? '',
                        'd' => $item['opsi']['d'] ?? '',
                    ];
                }

                Soal::create([
                    'materi_id' => $kuis->id,
                    'tipe_soal' => $item['tipe'],
                    'pertanyaan' => $item['pertanyaan'],
                    'gambar' => $pathGambar,
                    'opsi_jawaban' => $opsi,
                    'kunci_jawaban' => $item['kunci'],
                    'bobot_nilai' => $item['bobot'] ?? 10,
                ]);
            }

            DB::commit();
            return redirect()->route('guru.materi.index')->with('success', 'Kuis berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update kuis: ' . $e->getMessage());
        }
    }
}