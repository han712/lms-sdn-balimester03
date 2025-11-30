<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use App\Models\User;

class GuruController extends Controller
{
    /**
     * Dashboard Guru
     */
    public function index()
    {
        // Ambil ID Guru yang sedang login
        $guruId = Auth::id();

        // Statistik utama
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_absensi' => Absensi::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count(),
            'total_jawaban' => JawabanKuis::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count(),
            'jawaban_belum_dinilai' => JawabanKuis::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->whereNull('nilai')->count(),
        ];

        // Data grafik (Materi per Kelas)
        $materi_per_kelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->toArray();

        // Kuis yang belum dinilai
        $kuis_pending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->whereNull('nilai')
            ->latest()
            ->take(5)
            ->get();

        // Materi terbaru
        $recent_materi = Materi::where('guru_id', $guruId)
            ->latest()
            ->take(5)
            ->get();

        // Absensi terbaru
        $recent_absensi = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->latest('waktu_akses')
            ->take(5)
            ->get();

        return view('guru.dashboard', compact(
            'stats',
            'materi_per_kelas',
            'kuis_pending',
            'recent_materi',
            'recent_absensi'
        ));
    }

    /**
     * ✅ Halaman Data Guru
     * Menampilkan semua guru:
     * - dari database (misalnya Budi)
     * - dan dari folder public/FotoGuru
     */
    public function dataGuru()
    {
        // Ambil guru dari database (contohnya Budi)
        $guruFromDB = User::where('role', 'guru')
            ->get()
            ->map(function ($guru) {
                return (object) [
                    'name' => $guru->name,
                    'tempat_lahir' => $guru->tempat_lahir ?? '-',
                    'tanggal_lahir' => $guru->tanggal_lahir ?? '-',
                    'agama' => $guru->agama ?? '-',
                    'nip' => $guru->nip ?? '-',
                    'mapel' => $guru->mapel ?? '-',
                    'foto_url' => $guru->foto
                        ? asset('FotoGuru/' . $guru->foto)
                        : 'https://via.placeholder.com/400x400?text=Guru',
                ];
            });

        // Ambil semua file di folder FotoGuru
        $path = public_path('FotoGuru');
        $files = File::exists($path) ? File::files($path) : [];

        // Ambil daftar guru dari folder (nama file)
        $guruFromFolder = collect($files)->map(function ($file) {
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return (object) [
                'name' => $filename,
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'agama' => null,
                'nip' => null,
                'mapel' => null,
                'foto_url' => asset('FotoGuru/' . $file->getFilename()),
            ];
        });

        // Gabungkan data dari DB + folder (hindari duplikat nama)
        $guruList = $guruFromDB->merge($guruFromFolder)
            ->unique('name')
            ->values();

        return view('guru.data-guru', compact('guruList'));
    }
}
