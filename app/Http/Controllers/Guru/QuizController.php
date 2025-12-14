<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\JawabanKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Daftar Kuis yang dibuat guru.
     */
    public function index()
    {
        $kuis = Materi::where('guru_id', auth()->id())
            ->where('tipe', 'kuis') // Filter khusus kuis
            ->withCount('jawabanKuis') // Hitung berapa siswa sudah jawab
            ->latest()
            ->paginate(10);

        return view('guru.kuis.index', compact('kuis'));
    }

    public function create()
    {
        return view('guru.kuis.create');
    }

    /**
     * Logic CREATE QUIZ (MakeQuizController Logic).
     */
    public function store(Request $request)
    {
        // Validasi mirip materi, tapi Kuis WAJIB punya tanggal selesai (deadline)
        $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string', // Soal kuis bisa ditaruh di sini atau file
            'kelas' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai', // Deadline
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Soal dalam bentuk file
        ]);

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('kuis', 'public');
            }

            Materi::create([
                'guru_id' => auth()->id(),
                'judul' => $request->judul,
                'keterangan' => $request->keterangan,
                'tipe' => 'kuis', // HARDCODE TIPE KUIS
                'kelas' => $request->kelas,
                'file' => $path,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_published' => true,
            ]);

            DB::commit();
            return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat kuis.');
        }
    }

    /**
     * Melihat hasil jawaban siswa untuk kuis tertentu.
     */
    public function show($id)
    {
        $kuis = Materi::with(['jawabanKuis.siswa', 'jawabanKuis' => function($q) {
            $q->orderBy('created_at', 'desc');
        }])
        ->where('id', $id)
        ->where('guru_id', auth()->id())
        ->where('tipe', 'kuis')
        ->firstOrFail();

        return view('guru.kuis.hasil', compact('kuis'));
    }

    /**
     * Menampilkan detail jawaban satu siswa untuk dinilai.
     */
    public function detailJawaban($jawabanId)
    {
        $jawaban = JawabanKuis::with(['materi', 'siswa'])->findOrFail($jawabanId);
        
        // Security check
        if ($jawaban->materi->guru_id !== auth()->id()) {
            abort(403);
        }

        return view('guru.kuis.detail-jawaban', compact('jawaban'));
    }

    /**
     * Logic Memberi Nilai (Grading).
     */
    public function nilaiJawaban(Request $request, $jawabanId)
    {
        $request->validate([
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string'
        ]);

        $jawaban = JawabanKuis::findOrFail($jawabanId);
        
        // Update Nilai
        $jawaban->update([
            'nilai' => $request->nilai,
            'catatan_guru' => $request->catatan_guru,
            'dinilai_pada' => now(),
            'dinilai_oleh' => auth()->id()
        ]);

        return redirect()->route('guru.kuis.show', $jawaban->materi_id)
            ->with('success', 'Nilai berhasil disimpan.');
    }
}