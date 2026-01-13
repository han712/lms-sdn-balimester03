<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $query = Materi::with('guru')
            ->where('kelas', Auth::user()->kelas)
            ->where('is_published', true);

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $materi = $query->latest()->paginate(9);

        return view('siswa.materi.index', compact('materi'));
    }

    public function show($id)
    {
        // PERBAIKAN 1: Tambahkan 'soals' di sini agar data soal terbawa
        $materi = Materi::with(['guru', 'soals'])->findOrFail($id);

        if ($materi->kelas != Auth::user()->kelas) {
            abort(403, 'Materi ini bukan untuk kelas Anda.');
        }

        // Catat Absensi (Hadir)
        Absensi::firstOrCreate([
            'siswa_id' => Auth::id(),
            'materi_id' => $materi->id,
        ], [
            'status' => 'hadir',
            'waktu_akses' => now(),
        ]);

        // Cek apakah siswa sudah pernah mengerjakan kuis ini
        $existingAnswer = JawabanKuis::where('materi_id', $materi->id)
            ->where('siswa_id', Auth::id())
            ->first();

        return view('siswa.materi.show', compact('materi', 'existingAnswer'));
    }

    public function submitKuis(Request $request, $id)
    {
        $materi = Materi::with('soals')->findOrFail($id);
        
        // Cek duplikasi submit
        $cek = JawabanKuis::where('materi_id', $id)->where('siswa_id', Auth::id())->first();
        if($cek) return back()->with('error', 'Anda sudah mengerjakan kuis ini!');

        // JIKA KUIS INTERAKTIF (Punya Soal di Database)
        if ($materi->soals->count() > 0) {
            $totalNilai = 0;
            $jawabanSiswaDetail = []; // Bisa disimpan JSON jika mau (opsional)

            foreach ($materi->soals as $soal) {
                // Ambil jawaban siswa dari form (name="jawaban[soal_id]")
                $jawabanInput = $request->input('jawaban.' . $soal->id);

                if ($soal->tipe_soal == 'pilihan_ganda') {
                    // Cek kunci jawaban
                    if (strtolower($jawabanInput) == strtolower($soal->kunci_jawaban)) {
                        $totalNilai += $soal->bobot_nilai;
                    }
                } 
                // Essay biasanya butuh penilaian manual guru, 
                // tapi di sini kita set 0 dulu atau logic lain.
            }

            // Simpan Hasil
            JawabanKuis::create([
                'materi_id' => $id,
                'siswa_id' => Auth::id(),
                'nilai' => $totalNilai, // Nilai otomatis untuk PG
                'file_path' => null, // Tidak ada file
                'catatan_guru' => 'Dinilai otomatis oleh sistem.',
            ]);

            return back()->with('success', 'Jawaban berhasil dikirim! Nilai Anda: ' . $totalNilai);
        } 
        
        // JIKA TUGAS UPLOAD FILE (Materi lama/manual)
        else {
            $request->validate([
                'file_jawaban' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]);

            $path = $request->file('file_jawaban')->store('tugas_siswa', 'public');

            JawabanKuis::create([
                'materi_id' => $id,
                'siswa_id' => Auth::id(),
                'file_path' => $path,
                'nilai' => null, // Menunggu guru
            ]);

            return back()->with('success', 'Tugas berhasil diupload!');
        }
    }
}