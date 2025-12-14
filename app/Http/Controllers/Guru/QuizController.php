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
        $kuis = Materi::where('guru_id', Auth::id())
            ->where('tipe', 'kuis')
            ->withCount('jawabanKuis')
            ->latest()
            ->paginate(10);

        return view('guru.kuis.index', compact('kuis'));
    }

    public function create()
    {
        return view('guru.kuis.create');
    }

    /**
     * Logic CREATE QUIZ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string', 
            'kelas' => 'required|in:1,2,3,4,5,6',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('materi/kuis', $filename, 'public');
            }

            Materi::create([
                'guru_id' => Auth::id(),
                'judul' => $request->judul,
                'keterangan' => $request->keterangan,
                'tipe' => 'kuis', // FIX: Tipe Kuis
                'kelas' => $request->kelas,
                'file' => $path, // FIX: file sesuai kolom DB
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_published' => true, // Kuis biasanya langsung publish, atau bisa dibuat toggle
            ]);

            DB::commit();
            return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('error', 'Gagal membuat kuis: ' . $e->getMessage())->withInput();
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