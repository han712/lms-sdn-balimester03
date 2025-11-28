<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'total_users' => User::count(),
            'total_admin' => User::superAdmin()->count(),
            'total_guru' => User::guru()->count(),
            'total_siswa' => User::siswa()->count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            
            'total_materi' => Materi::count(),
            'published_materi' => Materi::where('is_published', true)->count(),
            'total_kuis' => Materi::where('tipe', 'kuis')->count(),
            
            'total_absensi' => Absensi::count(),
            'absensi_hadir' => Absensi::where('status', 'hadir')->count(),
            
            'total_jawaban' => JawabanKuis::count(),
            'jawaban_dinilai' => JawabanKuis::whereNotNull('nilai')->count(),
        ];

        // Recent activities
        $recent_users = User::latest()->take(5)->get();
        $recent_materi = Materi::with('guru')->latest()->take(5)->get();
        
        // Siswa per kelas
        $siswa_per_kelas = [];
        for ($i = 1; $i <= 6; $i++) {
            $siswa_per_kelas[$i] = User::siswa()->where('kelas', (string)$i)->count();
        }

        // Chart data for absensi
        $absensi_chart = [
            'labels' => ['Hadir', 'Tidak Hadir', 'Sakit', 'Izin'],
            'data' => [
                Absensi::where('status', 'hadir')->count(),
                Absensi::where('status', 'tidak_hadir')->count(),
                Absensi::where('status', 'sakit')->count(),
                Absensi::where('status', 'izin')->count(),
            ]
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recent_users',
            'recent_materi',
            'siswa_per_kelas',
            'absensi_chart'
        ));
    }
}