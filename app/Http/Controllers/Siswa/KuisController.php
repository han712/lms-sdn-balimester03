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
     * Submit Jawaban Kuis (PG auto nilai, Essay menunggu guru)
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

            // =========================
            // SKENARIO A: KUIS INTERAKTIF (ada soal)
            // =========================
            if ($materi->soals->count() > 0) {

                // Minimal validasi: harus ada array jawaban
                $request->validate([
                    'jawaban' => 'required|array',
                ]);

                $detailJawaban = [];
                $totalBobotPG = 0;
                $skorPG = 0;
                $adaEssay = false;

                foreach ($materi->soals as $soal) {
                    $jawabanInput = $request->input('jawaban.' . $soal->id);

                    // simpan detail jawaban (PG + Essay)
                    $detailJawaban[$soal->id] = [
                        'tipe' => $soal->tipe_soal,
                        'jawaban' => $jawabanInput,
                        'kunci' => $soal->kunci_jawaban,
                        'bobot' => (int) $soal->bobot_nilai,
                    ];

                    // ESSAY: tidak dinilai otomatis
                    if ($soal->tipe_soal === 'essay') {
                        $adaEssay = true;
                        continue;
                    }

                    // PG: dihitung otomatis
                    $totalBobotPG += (int) $soal->bobot_nilai;

                    if ($jawabanInput !== null && strtoupper(trim($jawabanInput)) === strtoupper(trim($soal->kunci_jawaban))) {
                        $skorPG += (int) $soal->bobot_nilai;
                    }
                }

                // Nilai:
                // - kalau ada essay -> NULL (menunggu guru)
                // - kalau semua PG -> nilai 0-100 (lebih enak dibanding jumlah bobot)
                $nilaiAkhir = null;

                if (!$adaEssay) {
                    $nilaiAkhir = $totalBobotPG > 0
                        ? round(($skorPG / $totalBobotPG) * 100)
                        : 0;
                }

                JawabanKuis::create([
                    'materi_id'     => $materi->id,
                    'siswa_id'      => $siswa->id,
                    'jawaban'       => $detailJawaban, // pastikan kolom jawaban json & model cast array
                    'nilai'         => $nilaiAkhir,
                    'catatan_guru'  => $adaEssay ? null : 'Nilai otomatis oleh sistem (PG).',
                    // kalau kamu punya kolom status, boleh aktifkan:
                    // 'status' => $adaEssay ? 'menunggu_penilaian' : 'selesai',
                ]);

                $msg = $adaEssay
                    ? 'Jawaban terkirim! Menunggu penilaian guru untuk soal essay.'
                    : 'Jawaban terkirim! Nilai kamu: ' . $nilaiAkhir;
            }

            // =========================
            // SKENARIO B: TUGAS UPLOAD FILE (Manual)
            // =========================
            else {
                $request->validate([
                    'file_jawaban' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
                    'catatan' => 'nullable|string|max:255'
                ]);

                $file = $request->file('file_jawaban');
                $filename = 'quiz_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('jawaban_kuis', $filename, 'public');

                JawabanKuis::create([
                    'materi_id'     => $materi->id,
                    'siswa_id'      => $siswa->id,
                    'jawaban_file'  => $path,
                    'catatan_siswa' => $request->catatan,
                    'nilai'         => null
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