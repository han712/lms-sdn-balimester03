<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Materi;
use App\Models\JawabanKuis;
use Carbon\Carbon;

class KuisController extends Controller
{
    /**
     * Logic Submit Jawaban (Support File Upload & Soal Interaktif)
     */
    public function store(Request $request, $id)
    {
        $siswa = Auth::user();
        $materi = Materi::with('soals')->findOrFail($id);

        if ($materi->tipe !== 'kuis') abort(404);

        // Validasi Deadline
        if ($materi->tanggal_selesai && Carbon::now()->greaterThan($materi->tanggal_selesai)) {
            return back()->with('error', 'Maaf, waktu pengerjaan kuis sudah habis! ⏳');
        }

        // Cek Double Submit
        $existing = JawabanKuis::where('materi_id', $materi->id)
            ->where('siswa_id', $siswa->id)
            ->exists();
            
        if ($existing) return back()->with('error', 'Kamu sudah mengirim jawaban sebelumnya.');

        try {
            DB::beginTransaction();

            // SKENARIO A: KUIS INTERAKTIF (Ada Soal di DB)
            if ($materi->soals->count() > 0) {
                $totalNilai = 0;
                foreach ($materi->soals as $soal) {
                    $jawabanInput = $request->input('jawaban.' . $soal->id);
                    // Cek Pilihan Ganda
                    if ($soal->tipe_soal == 'pilihan_ganda') {
                        if (strtoupper($jawabanInput) == strtoupper($soal->kunci_jawaban)) {
                            $totalNilai += $soal->bobot_nilai;
                        }
                    }
                }

                JawabanKuis::create([
                    'materi_id' => $materi->id,
                    'siswa_id' => $siswa->id,
                    'nilai' => $totalNilai, // Nilai langsung keluar
                    'catatan_guru' => 'Nilai otomatis oleh sistem.',
                ]);
                
                $msg = 'Jawaban terkirim! Nilai kamu: ' . $totalNilai;
            } 
            
            // SKENARIO B: TUGAS UPLOAD FILE (Manual)
            else {
                $request->validate([
                    'file_jawaban' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
                    'catatan' => 'nullable|string|max:255'
                ]);

                $file = $request->file('file_jawaban');
                $filename = 'quiz_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('jawaban_kuis', $filename, 'public');

                JawabanKuis::create([
                    'materi_id' => $materi->id,
                    'siswa_id' => $siswa->id,
                    'jawaban_file' => $path,
                    'catatan_siswa' => $request->catatan,
                    'nilai' => null // Menunggu guru
                ]);

                $msg = 'Tugas berhasil diupload! Menunggu penilaian guru.';
            }

            DB::commit();
            return redirect()->route('siswa.materi.show', $materi->id)->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Riwayat Kuis & Nilai
     */
    public function history()
    {
        $riwayat = JawabanKuis::with('materi')
            ->where('siswa_id', Auth::id())
            ->latest()
            ->paginate(15);
            
        $stats = [
            'total_kuis' => $riwayat->total(),
            'rata_rata' => JawabanKuis::where('siswa_id', Auth::id())->avg('nilai') ?? 0,
            'tertinggi' => JawabanKuis::where('siswa_id', Auth::id())->max('nilai') ?? 0,
        ];

        return view('siswa.riwayat.kuis', compact('riwayat', 'stats'));
    }
}