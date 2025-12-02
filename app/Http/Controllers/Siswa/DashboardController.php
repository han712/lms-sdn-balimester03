<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::user();
        $kelas = $siswa->kelas;
        
        // 1. Statistik Utama (Cards)
        $stats = [
            // Hitung materi yang tersedia untuk kelas ini
            'total_materi' => Materi::where('kelas', $kelas)
                ->where('is_published', true)
                ->count(),
                
            // Hitung berapa materi yang sudah "dibuka" (ada record absensi status hadir)
            'materi_diakses' => Absensi::where('siswa_id', $siswa->id)
                ->where('status', 'hadir')
                ->count(),
            
            // Hitung kuis yang sudah disubmit
            'kuis_dijawab' => JawabanKuis::where('siswa_id', $siswa->id)->count(),
            
            // Rata-rata nilai (hanya dari yang sudah dinilai)
            'rata_nilai' => JawabanKuis::where('siswa_id', $siswa->id)
                ->whereNotNull('nilai')
                ->avg('nilai') ?? 0,
        ];

        // 2. Materi Terbaru (Max 6 item)
        // Kita exclude materi yang kadaluarsa (jika ada tanggal selesai)
        $available_materi = Materi::where('kelas', $kelas)
            ->where('is_published', true)
            ->with(['guru:id,name']) // Eager load nama guru
            ->latest()
            ->take(6)
            ->get();

        // 3. Kuis "Pending" (Misi Kamu)
        // Logic: Cari materi tipe kuis di kelas ini, yang user BELUM punya jawabanKuis
        $kuis_pending = Materi::where('kelas', $kelas)
            ->where('is_published', true)
            ->where('tipe', 'kuis')
            // Filter kuis yang masih aktif (belum lewat deadline)
            ->where(function($q) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', Carbon::now());
            })
            // Filter dimana user belum mengumpulkan jawaban
            ->whereDoesntHave('jawabanKuis', function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->orderBy('tanggal_selesai', 'asc') // Urutkan dari deadline terdekat
            ->take(3)
            ->get();

        // 4. Riwayat Terakhir (Gabungan Absensi & Kuis untuk Timeline)
        $recent_absensi = Absensi::with('materi')
            ->where('siswa_id', $siswa->id)
            ->latest('waktu_akses')
            ->take(3)
            ->get();

        $recent_nilai = JawabanKuis::with('materi')
            ->where('siswa_id', $siswa->id)
            ->whereNotNull('nilai')
            ->latest('dinilai_pada')
            ->take(3)
            ->get();

        return view('siswa.dashboard', compact(
            'stats',
            'available_materi',
            'kuis_pending',
            'recent_absensi',
            'recent_nilai'
        ));
    }
}