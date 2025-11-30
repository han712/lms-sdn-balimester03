<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// Jangan lupa import Model yang dibutuhkan
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;

class GuruController extends Controller
{
    public function index()
    {
        // Ambil ID Guru yang sedang login
        $guruId = Auth::id();

        // 1. STATISTIK UTAMA (Cards)
        // Kita gunakan query scope / where untuk memastikan data milik guru ini saja
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            
            'published_materi' => Materi::where('guru_id', $guruId)
                ->where('is_published', true)->count(),
                
            'draft_materi' => Materi::where('guru_id', $guruId)
                ->where('is_published', false)->count(),
                
            'total_kuis' => Materi::where('guru_id', $guruId)
                ->where('tipe', 'kuis')->count(),
            
            // Hitung absensi hanya pada materi milik guru ini
            'total_absensi' => Absensi::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count(),

            // Hitung jawaban kuis pada materi milik guru ini
            'total_jawaban' => JawabanKuis::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count(),
            
            'jawaban_belum_dinilai' => JawabanKuis::whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->whereNull('nilai')->count(),
        ];

        // 2. DATA UNTUK GRAFIK (Materi per Kelas)
        // Hasilnya array: ['1' => 5, '2' => 3, ...]
        $materi_per_kelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->toArray();

        // 3. DAFTAR KUIS YANG PERLU DINILAI (Pending Review)
        $kuis_pending = JawabanKuis::with(['siswa', 'materi']) // Eager load relasi biar cepat
            ->whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->whereNull('nilai')
            ->latest()
            ->take(5)
            ->get();

        // 4. MATERI TERBARU (Recent Activity)
        $recent_materi = Materi::where('guru_id', $guruId)
            ->latest()
            ->take(5)
            ->get();

        // 5. ABSENSI TERBARU
        $recent_absensi = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->latest('waktu_akses') // Pastikan nama kolom timestamp-nya benar
            ->take(5)
            ->get();

        // Kirim semua variabel ke View
        return view('guru.dashboard', compact(
            'stats',
            'materi_per_kelas',
            'kuis_pending',
            'recent_materi',
            'recent_absensi'
        ));
    }
}