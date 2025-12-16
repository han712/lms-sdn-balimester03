<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\JawabanKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Daftar Kuis yang dibuat guru.
     */
    public function index()
    {
        // Menampilkan daftar materi yang bertipe 'kuis'
        $kuis = Materi::where('tipe', 'kuis')
            ->where('guru_id', auth()->id())
            ->latest()
            ->paginate(10);
            
        return view('guru.kuis.index', compact('kuis')); // Anda perlu buat view index khusus kuis jika mau
    }

    public function create()
    {
        // Menampilkan form pembuatan kuis interaktif
        return view('guru.kuis.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Header Kuis
        $request->validate([
            'judul' => 'required|string|max:255',
            'kelas' => 'required',
            'deskripsi' => 'nullable|string',
            // Validasi Array Soal
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.tipe' => 'required|in:pilihan_ganda,essay',
            'soal.*.kunci' => 'required', // Kunci jawaban wajib
            'soal.*.gambar' => 'nullable|image|max:2048', // Max 2MB per gambar
        ]);

        DB::beginTransaction();

        try {
            // 2. Simpan Header Kuis sebagai 'Materi'
            $materi = Materi::create([
                'guru_id' => auth()->id(),
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'kelas' => $request->kelas,
                'tipe' => 'kuis', // Flagging sebagai kuis
                'is_published' => false, // Draft dulu
            ]);

            // 3. Looping Simpan Tiap Soal
            foreach ($request->soal as $index => $item) {
                
                $pathGambar = null;
                
                // Handle Image Upload per Soal
                if ($request->hasFile("soal.{$index}.gambar")) {
                    $file = $request->file("soal.{$index}.gambar");
                    $pathGambar = $file->store('soal-images', 'public');
                }

                // Siapkan opsi jawaban jika PG
                $opsi = null;
                if ($item['tipe'] === 'pilihan_ganda') {
                    $opsi = [
                        'a' => $item['opsi']['a'] ?? '',
                        'b' => $item['opsi']['b'] ?? '',
                        'c' => $item['opsi']['c'] ?? '',
                        'd' => $item['opsi']['d'] ?? '',
                    ];
                }

                // Simpan ke tabel Soal
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

            return redirect()->route('guru.materi.index')->with('success', 'Kuis berhasil dibuat! Silahkan publish agar siswa bisa mengerjakan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat kuis: ' . $e->getMessage());
        }
    }
    /**
     * Melihat hasil jawaban siswa untuk kuis tertentu.
     * Menggunakan Route Model Binding (Materi $materi) agar lebih clean.
     */
    public function hasil(Materi $materi)
    {
        // Security Check
        if ($materi->guru_id !== Auth::id()) abort(403);
        if ($materi->tipe !== 'kuis') abort(404);

        $materi->load(['jawabanKuis.siswa', 'jawabanKuis' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        // Hitung statistik sederhana
        $stats = [
            'total_submit' => $materi->jawabanKuis->count(),
            'rata_rata' => round($materi->jawabanKuis->avg('nilai') ?? 0, 1),
            'tertinggi' => $materi->jawabanKuis->max('nilai') ?? 0,
            'terendah' => $materi->jawabanKuis->min('nilai') ?? 0,
        ];

        return view('guru.kuis.hasil', compact('materi', 'stats'));
    }

    /**
     * Menampilkan detail jawaban satu siswa untuk dinilai.
     */
    public function detailJawaban(JawabanKuis $jawaban)
    {
        // Eager load relasi
        $jawaban->load(['materi', 'siswa']);
        
        // Security check: pastikan guru yg login adalah pemilik materi kuis ini
        if ($jawaban->materi->guru_id !== Auth::id()) {
            abort(403);
        }

        return view('guru.kuis.detail-jawaban', compact('jawaban'));
    }

    /**
     * Logic Memberi Nilai (Grading).
     */
    public function nilaiJawaban(Request $request, JawabanKuis $jawaban)
    {
        $request->validate([
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string'
        ]);

        // Security check
        if ($jawaban->materi->guru_id !== Auth::id()) {
            abort(403);
        }
        
        // Update Nilai
        $jawaban->update([
            'nilai' => $request->nilai,
            'catatan_guru' => $request->catatan_guru,
            'dinilai_pada' => now(),
            'dinilai_oleh' => Auth::id()
        ]);

        return redirect()->route('guru.kuis.hasil', $jawaban->materi_id)
            ->with('success', 'Nilai berhasil disimpan.');
    }
}