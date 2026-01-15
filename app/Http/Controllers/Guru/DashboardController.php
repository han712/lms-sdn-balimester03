<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $guruId = Auth::id();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Statistik Utama
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_video' => Materi::where('guru_id', $guruId)
                ->where('tipe', 'materi')
                ->whereNotNull('file') // Menggunakan 'file' sesuai perbaikan database
                ->where(function($q) {
                    $q->where('file', 'like', '%.mp4')
                      ->orWhere('file', 'like', '%.avi');
                })
                ->count(),
        ];

        // 2. Statistik Absensi
        $absensiStats = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir
            ")
            ->first();

        $stats['total_absensi'] = $absensiStats->total ?? 0;
        $stats['absensi_hadir'] = $absensiStats->hadir ?? 0;
        $stats['absensi_tidak_hadir'] = $absensiStats->tidak_hadir ?? 0;

        // 3. Statistik Kuis
        $kuisStats = JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->selectRaw("
                COUNT(*) as total_jawaban,
                SUM(CASE WHEN nilai IS NULL THEN 1 ELSE 0 END) as belum_dinilai,
                SUM(CASE WHEN nilai IS NOT NULL THEN 1 ELSE 0 END) as sudah_dinilai,
                AVG(CASE WHEN nilai IS NOT NULL THEN nilai END) as rata_rata_nilai
            ")
            ->first();

        $stats['total_jawaban'] = $kuisStats->total_jawaban ?? 0;
        $stats['jawaban_belum_dinilai'] = $kuisStats->belum_dinilai ?? 0;
        $stats['jawaban_sudah_dinilai'] = $kuisStats->sudah_dinilai ?? 0;
        $stats['rata_rata_nilai'] = round($kuisStats->rata_rata_nilai ?? 0, 2);

        // 4. Grafik & Charts
        $materiPerKelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->orderBy('kelas')
            ->get()
            ->mapWithKeys(fn($item) => [$item->kelas => $item->total])
            ->toArray();

        $materiPerBulan = Materi::where('guru_id', $guruId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('MONTH(created_at) as bulan, YEAR(created_at) as tahun, count(*) as total')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->map(fn($item) => [
                'label' => Carbon::create($item->tahun, $item->bulan)->format('M Y'),
                'total' => $item->total
            ])
            ->toArray();

        $absensiPerStatus = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereMonth('waktu_akses', $currentMonth)
            ->whereYear('waktu_akses', $currentYear)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->total])
            ->toArray();

        // 5. Data List (Tables)
        $kuisPending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('nilai')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get()
            ->map(function($jawaban) {
                $daysSinceSubmit = Carbon::parse($jawaban->created_at)->diffInDays(Carbon::now());
                $jawaban->priority = $daysSinceSubmit > 7 ? 'high' : ($daysSinceSubmit > 3 ? 'medium' : 'low');
                $jawaban->days_waiting = $daysSinceSubmit;
                return $jawaban;
            });

        $recentMateri = Materi::where('guru_id', $guruId)
            ->withCount(['absensi', 'jawabanKuis'])
            ->latest()
            ->take(5)
            ->get();

        $recentAbsensi = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->latest('waktu_akses')
            ->take(10)
            ->get();

        $siswaAktif = Absensi::with('siswa')
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->where('status', 'hadir')
            ->whereMonth('waktu_akses', $currentMonth)
            ->select('siswa_id', DB::raw('count(*) as total_hadir'))
            ->groupBy('siswa_id')
            ->orderByDesc('total_hadir')
            ->take(5)
            ->get();

        // --- TAMBAHAN YANG HILANG (FIX ERROR) ---
        
        // 6. Siswa Perlu Perhatian (Banyak tidak_hadir)
        $siswaPerluPerhatian = Absensi::with('siswa')
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->where('status', 'tidak_hadir')
            ->whereMonth('waktu_akses', $currentMonth)
            ->select('siswa_id', DB::raw('count(*) as total_tidak_hadir'))
            ->groupBy('siswa_id')
            ->having('total_tidak_hadir', '>=', 2) // Minimal 2x tidak_hadir
            ->orderByDesc('total_tidak_hadir')
            ->take(5)
            ->get();

        // 7. Aktivitas Guru (Statistik Bawah)
        $aktivitasGuru = [
            'materi_dibuat_bulan_ini' => Materi::where('guru_id', $guruId)
                ->whereMonth('created_at', $currentMonth)
                ->count(),
            'kuis_dinilai_bulan_ini' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
                ->whereNotNull('nilai')
                ->whereMonth('dinilai_pada', $currentMonth)
                ->count(),
            'total_login_bulan_ini' => 0, // Placeholder jika belum ada log login
        ];

        return view('guru.dashboard', compact(
            'stats', 
            'materiPerKelas', 
            'materiPerBulan', 
            'absensiPerStatus',
            'kuisPending', 
            'recentMateri', 
            'recentAbsensi', 
            'siswaAktif',
            'siswaPerluPerhatian', // <-- Variable ini sekarang sudah ada
            'aktivitasGuru'        // <-- Variable ini sekarang sudah ada
        ));
    }
}