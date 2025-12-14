<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        // Statistik utama
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_video' => Materi::where('guru_id', $guruId)
                ->where('tipe', 'materi')
                ->whereNotNull('file_path') // Sesuaikan dengan kolom di DB (file_path)
                ->where(function($q) {
                    $q->where('file_path', 'like', '%.mp4')
                      ->orWhere('file_path', 'like', '%.avi');
                })
                ->count(),
        ];

        // Statistik absensi
        $absensiStats = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
            ")
            ->first();

        $stats['total_absensi'] = $absensiStats->total ?? 0;
        $stats['absensi_hadir'] = $absensiStats->hadir ?? 0;
        $stats['absensi_alpha'] = $absensiStats->alpha ?? 0;

        // Statistik kuis
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

        // Grafik materi per kelas
        $materiPerKelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->orderBy('kelas')
            ->get()
            ->mapWithKeys(fn($item) => [$item->kelas => $item->total])
            ->toArray();

        // Grafik materi per bulan
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

        // Grafik absensi per status
        $absensiPerStatus = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereMonth('waktu_akses', $currentMonth)
            ->whereYear('waktu_akses', $currentYear)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->total])
            ->toArray();

        // Kuis Pending
        $kuisPending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('nilai')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        // Materi Terbaru
        $recentMateri = Materi::where('guru_id', $guruId)
            ->withCount(['absensi', 'jawabanKuis'])
            ->latest()
            ->take(5)
            ->get();

        // Absensi Terbaru
        $recentAbsensi = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->latest('waktu_akses')
            ->take(10)
            ->get();

        // Siswa Aktif
        $siswaAktif = Absensi::with('siswa')
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->where('status', 'hadir')
            ->whereMonth('waktu_akses', $currentMonth)
            ->select('siswa_id', DB::raw('count(*) as total_hadir'))
            ->groupBy('siswa_id')
            ->orderByDesc('total_hadir')
            ->take(5)
            ->get();

        return view('guru.dashboard', compact(
            'stats', 'materiPerKelas', 'materiPerBulan', 'absensiPerStatus',
            'kuisPending', 'recentMateri', 'recentAbsensi', 'siswaAktif'
        ));
    }
}
