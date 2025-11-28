<?php
// app/Http/Controllers/Siswa/DashboardController.php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $siswa = auth()->user();
        $kelas = $siswa->kelas;
        
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
            
            'kuis_dinilai' => JawabanKuis::where('siswa_id', $siswa->id)
                ->whereNotNull('nilai')
                ->count(),
                
            'rata_nilai' => JawabanKuis::where('siswa_id', $siswa->id)
                ->whereNotNull('nilai')
                ->avg('nilai'),
        ];

        // Materi terbaru yang tersedia
        $available_materi = Materi::where('kelas', $kelas)
            ->where('is_published', true)
            ->where('tanggal_mulai', '<=', Carbon::now())
            ->where(function($q) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', Carbon::now());
            })
            ->latest()
            ->take(6)
            ->get();

        // Kuis yang belum dikerjakan
        $kuis_pending = Materi::where('kelas', $kelas)
            ->where('is_published', true)
            ->where('tipe', 'kuis')
            ->where('tanggal_mulai', '<=', Carbon::now())
            ->whereDoesntHave('jawabanKuis', function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->take(5)
            ->get();

        // Riwayat absensi terakhir
        $recent_absensi = Absensi::with('materi')
            ->where('siswa_id', $siswa->id)
            ->latest('waktu_akses')
            ->take(5)
            ->get();

        // Nilai kuis terakhir
        $recent_nilai = JawabanKuis::with('materi')
            ->where('siswa_id', $siswa->id)
            ->whereNotNull('nilai')
            ->latest('dinilai_pada')
            ->take(5)
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