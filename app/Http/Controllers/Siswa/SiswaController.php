<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Carbon\Carbon;

class SiswaController extends Controller
{
    /**
     * Menampilkan daftar materi & kuis
     * Fitur: Search, Filter Mapel, Status (Sudah/Belum Dibaca)
     */
    public function index(Request $request)
    {
        $siswa = Auth::user();
        
        $query = Materi::where('kelas', $siswa->kelas)
            ->where('is_published', true);

        // Filter: Pencarian Judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter: Tipe (Materi/Kuis)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter: Mapel (Jika ada kolom mapel)
        if ($request->filled('mapel')) {
            $query->where('mapel', $request->mapel);
        }

        // Eager Loading & Sorting
        // Kita cek apakah siswa sudah pernah akses materi ini (untuk UI status 'Selesai/Belum')
        $materi = $query->with(['guru:id,name', 'absensi' => function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->withExists(['jawabanKuis as is_submitted' => function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('siswa.materi.index', compact('materi'));
    }

    /**
     * Menampilkan detail materi
     * LOGIC PENTING: Otomatis mencatat kehadiran (Absensi) saat siswa membuka halaman ini.
     */
    public function show(Materi $materi)
    {
        $siswa = Auth::user();

        // 1. Validasi Akses (Hanya siswa kelas bersangkutan)
        if ($materi->kelas != $siswa->kelas) {
            abort(403, 'Materi ini bukan untuk kelas kamu.');
        }

        // 2. Logic Absensi Otomatis
        // Cek apakah sudah ada record absensi
        $absensi = Absensi::where('materi_id', $materi->id)
            ->where('siswa_id', $siswa->id)
            ->first();
        
        $now = Carbon::now();

        if (!$absensi) {
            // Jika belum ada, buat baru (Hadir)
            Absensi::create([
                'materi_id' => $materi->id,
                'siswa_id' => $siswa->id,
                'status' => 'hadir',
                'waktu_akses' => $now,
            ]);
        } else {
            // Jika status sebelumnya tidak_hadir (dibuat otomatis oleh sistem), ubah jadi hadir
            if ($absensi->status === 'tidak_hadir' || $absensi->waktu_akses === null) {
            $absensi->update([
                'status' => 'hadir',
                'waktu_akses' => $now,
            ]);
            }
    }

        // 3. Cek Status Kuis (Jika tipe kuis)
        $existingAnswer = null;
        if ($materi->tipe == 'kuis') {
            $existingAnswer = JawabanKuis::where('materi_id', $materi->id)
                ->where('siswa_id', $siswa->id)
                ->first();
        }

        return view('siswa.materi.show', compact('materi', 'existingAnswer'));
    }

    /**
     * Logic Mengirim Jawaban Kuis
     */
    public function submitKuis(Request $request, Materi $materi)
    {
        $siswa = Auth::user();

        // Validasi Dasar
        if ($materi->tipe !== 'kuis') {
            abort(404);
        }

        // Validasi Deadline
        if ($materi->tanggal_selesai && Carbon::now()->greaterThan($materi->tanggal_selesai)) {
            return back()->with('error', 'Maaf, waktu pengerjaan kuis sudah habis! ⏳');
        }

        // Validasi Input
        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // Max 5MB
            'catatan' => 'nullable|string|max:255'
        ]);

        // Cek Double Submit
        $existing = JawabanKuis::where('materi_id', $materi->id)
            ->where('siswa_id', $siswa->id)
            ->exists();
            
        if ($existing) {
            return back()->with('error', 'Kamu sudah mengirim jawaban sebelumnya.');
        }

        try {
            DB::beginTransaction();

            // Upload File
            $file = $request->file('file_jawaban');
            $filename = 'quiz_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('jawaban_kuis', $filename, 'public');

            // Simpan ke Database
            JawabanKuis::create([
                'materi_id' => $materi->id,
                'siswa_id' => $siswa->id,
                'jawaban_file' => $path,
                'catatan_siswa' => $request->catatan,
                // Nilai null dulu, menunggu guru
            ]);

            DB::commit();

            return redirect()->route('siswa.materi.show', $materi->id)
                ->with('success', 'Jawaban berhasil dikirim! Tunggu nilai dari guru ya. 🌟');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Riwayat Absensi
     */
    public function riwayatAbsensi()
    {
        $riwayat = Absensi::with('materi')
            ->where('siswa_id', Auth::id())
            ->latest('waktu_akses')
            ->paginate(15);

        return view('siswa.riwayat.absensi', compact('riwayat'));
    }

    /**
     * Halaman Riwayat Kuis & Nilai
     */
    public function riwayatKuis()
    {
        $riwayat = JawabanKuis::with('materi')
            ->where('siswa_id', Auth::id())
            ->latest()
            ->paginate(15);
            
        // Statistik ringkas untuk header halaman
        $stats = [
            'total_kuis' => $riwayat->total(),
            'rata_rata' => JawabanKuis::where('siswa_id', Auth::id())->avg('nilai') ?? 0,
            'tertinggi' => JawabanKuis::where('siswa_id', Auth::id())->max('nilai') ?? 0,
        ];

        return view('siswa.riwayat.kuis', compact('riwayat', 'stats'));
    }
}