<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use App\Models\User;
use Carbon\Carbon;

class GuruController extends Controller
{
    // ========================================
    // DASHBOARD - IMPROVED
    // ========================================
    
    public function dashboard()
    {
        $guruId = Auth::id();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Statistik utama dengan optimasi query
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_video' => Materi::where('guru_id', $guruId)
            ->where('tipe', 'materi')
            ->whereNotNull('file') 
            ->where(function($q) {
                $q->where('file', 'like', '%.mp4')
                ->orWhere('file', 'like', '%.avi');
            })
            ->count(),
        ];

        // Statistik absensi
        $absensiStats = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
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

        // Grafik materi per bulan (6 bulan terakhir)
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

        // Grafik absensi per status (bulan ini)
        $absensiPerStatus = Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereMonth('waktu_akses', $currentMonth)
            ->whereYear('waktu_akses', $currentYear)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->total])
            ->toArray();

        // Kuis yang perlu dinilai dengan prioritas
        $kuisPending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('nilai')
            ->orderBy('created_at', 'asc') // Yang paling lama duluan
            ->take(10)
            ->get()
            ->map(function($jawaban) {
                $daysSinceSubmit = Carbon::parse($jawaban->created_at)->diffInDays(Carbon::now());
                $jawaban->priority = $daysSinceSubmit > 7 ? 'high' : ($daysSinceSubmit > 3 ? 'medium' : 'low');
                $jawaban->days_waiting = $daysSinceSubmit;
                return $jawaban;
            });

        // Materi terbaru dengan statistik
        $recentMateri = Materi::where('guru_id', $guruId)
            ->withCount([
                'absensi',
                'absensi as absensi_hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'jawabanKuis',
                'jawabanKuis as jawaban_dinilai_count' => fn($q) => $q->whereNotNull('nilai')
            ])
            ->latest()
            ->take(5)
            ->get();

        // Absensi terbaru
        $recentAbsensi = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->latest('waktu_akses')
            ->take(10)
            ->get();

        // Siswa paling aktif (berdasarkan kehadiran)
        $siswaAktif = Absensi::with('siswa')
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->where('status', 'hadir')
            ->whereMonth('waktu_akses', $currentMonth)
            ->whereYear('waktu_akses', $currentYear)
            ->select('siswa_id', DB::raw('count(*) as total_hadir'))
            ->groupBy('siswa_id')
            ->orderByDesc('total_hadir')
            ->take(5)
            ->get();

        // Siswa yang perlu perhatian (banyak alpha)
        $siswaPerluPerhatian = Absensi::with('siswa')
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->where('status', 'alpha')
            ->whereMonth('waktu_akses', $currentMonth)
            ->whereYear('waktu_akses', $currentYear)
            ->select('siswa_id', DB::raw('count(*) as total_alpha'))
            ->groupBy('siswa_id')
            ->having('total_alpha', '>=', 3)
            ->orderByDesc('total_alpha')
            ->take(5)
            ->get();

        // Aktivitas guru (untuk tracking)
        $aktivitasGuru = [
            'total_login_bulan_ini' => $this->getLoginCountThisMonth($guruId),
            'materi_dibuat_bulan_ini' => Materi::where('guru_id', $guruId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),
            'kuis_dinilai_bulan_ini' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
                ->whereMonth('dinilai_pada', $currentMonth)
                ->whereYear('dinilai_pada', $currentYear)
                ->count(),
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
            'siswaPerluPerhatian',
            'aktivitasGuru'
        ));
    }


    /**
     * ======================================
     * DATA GURU (Foto Folder + Biodata JSON)
     * ======================================
     */
    public function dataGuru()
    {
        // FILE JSON: public/dataGuru.json
        $jsonPath = public_path('dataGuru.json');

        $guruData = file_exists($jsonPath)
            ? collect(json_decode(file_get_contents($jsonPath), true))
            : collect([]);

        // FOTO: public/FotoGuru
        $path = public_path('FotoGuru');
        $files = File::exists($path) ? File::files($path) : [];

        // GABUNG FOTO + JSON
        $guruList = collect($files)->map(function ($file) use ($guruData) {

            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // cari data di JSON berdasarkan nama foto
            $info = $guruData->firstWhere('name', $name);

            return (object)[
                'name' => $name,
                'foto_url' => asset('FotoGuru/' . $file->getFilename()),
                'tempat_lahir' => $info['tempat_lahir'] ?? '-',
                'tanggal_lahir' => $info['tanggal_lahir'] ?? '-',
                'agama' => $info['agama'] ?? '-',
                'nip' => $info['nip'] ?? '-',
                'mapel' => $info['mapel'] ?? '-'
            ];
        });

        return view('guru.data-guru', compact('guruList'));
    }


    /**
     * ======================================
     * HALAMAN MATERI
     * ======================================
     */
    public function materiIndex()
    {
        $materi = Materi::where('guru_id', Auth::id())
            ->when(request('search'), function ($q) {
                $q->where('judul', 'like', '%' . request('search') . '%')
                  ->orWhere('deskripsi', 'like', '%' . request('search') . '%');
            })
            ->when(request('kelas'), fn($q) => $q->where('kelas', request('kelas')))
            ->when(request('tipe'), fn($q) => $q->where('tipe', request('tipe')))
            ->when(request('status'), function ($q) {
                $is_published = request('status') === 'published';
                $q->where('is_published', $is_published);
            })
            ->latest()
            ->paginate(15);

        // Filter tipe
        if (request('tipe')) {
            $query->where('tipe', request('tipe'));
        }

        // Filter status
        if (request('status')) {
            $isPublished = request('status') === 'published';
            $query->where('is_published', $isPublished);
        }

        // Filter tanggal
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Sorting
        $sortBy = request('sort_by', 'created_at');
        $sortOrder = request('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Dengan statistik
        $materi = $query->withCount([
                'absensi',
                'absensi as absensi_hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'jawabanKuis',
                'jawabanKuis as jawaban_belum_dinilai_count' => fn($q) => $q->whereNull('nilai')
            ])
            ->paginate(request('per_page', 15))
            ->appends(request()->query());

        // Statistik untuk filter
        $filterStats = [
            'total' => Materi::where('guru_id', Auth::id())->count(),
            'published' => Materi::where('guru_id', Auth::id())->where('is_published', true)->count(),
            'draft' => Materi::where('guru_id', Auth::id())->where('is_published', false)->count(),
            'materi' => Materi::where('guru_id', Auth::id())->where('tipe', 'materi')->count(),
            'kuis' => Materi::where('guru_id', Auth::id())->where('tipe', 'kuis')->count(),
        ];

        return view('guru.materi.index', compact('materi', 'filterStats'));
    }

    public function create()
    {
        // Data untuk dropdown kelas
        $kelasList = range(1, 6);
        
        return view('guru.materi.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:5000',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200',
            'is_published' => 'boolean',
            'tanggal_deadline' => 'nullable|date|after:today', // Untuk kuis
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'guru_id' => Auth::id(),
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'is_published' => $request->boolean('is_published'),
            ];

            // Handle deadline untuk kuis
            if ($validated['tipe'] === 'kuis' && isset($validated['tanggal_deadline'])) {
                $materiData['tanggal_deadline'] = $validated['tanggal_deadline'];
            }

            // Handle file upload dengan validasi lebih ketat
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validasi tambahan
                if (!$file->isValid()) {
                    throw new \Exception('File tidak valid');
                }

                // Generate unique filename
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                
                // Store dengan folder berdasarkan tipe
                $folder = $validated['tipe'] === 'kuis' ? 'materi/kuis' : 'materi/pembelajaran';
                $path = $file->storeAs($folder, $filename, 'public');
                
                $materiData['file_path'] = $path;
                $materiData['file_size'] = $file->getSize();
                $materiData['file_type'] = $file->getMimeType();
            }

            $materi = Materi::create($data);

            // Auto-create absensi untuk materi yang dipublish
            if ($materi->is_published && $materi->tipe === 'materi') {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            // Log activity
            Log::info("Materi created by guru", [
                'guru_id' => Auth::id(),
                'materi_id' => $materi->id,
                'judul' => $materi->judul
            ]);

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to create materi", [
                'guru_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'Gagal membuat materi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Materi $materi)
    {
        $this->authorizeMateri($materi);

        // Load relations dengan statistik
        $materi->load([
            'absensi' => fn($q) => $q->with('siswa')->latest('waktu_akses'),
            'jawabanKuis' => fn($q) => $q->with('siswa')->latest()
        ]);

        // Statistik absensi
        $absensiStats = $materi->absensi()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
            ")
            ->first();

        // Statistik kuis
        $kuisStats = null;
        if ($materi->tipe === 'kuis') {
            $jawaban = $materi->jawabanKuis();
            $jawabanDinilai = $jawaban->whereNotNull('nilai');
            
            $kuisStats = [
                'total_siswa_kelas' => User::where('role', 'siswa')
                    ->where('kelas', $materi->kelas)
                    ->where('is_active', true)
                    ->count(),
                'total_jawaban' => $jawaban->count(),
                'sudah_dinilai' => $jawabanDinilai->count(),
                'belum_dinilai' => $jawaban->whereNull('nilai')->count(),
                'rata_rata' => round($jawabanDinilai->avg('nilai') ?? 0, 2),
                'nilai_tertinggi' => $jawabanDinilai->max('nilai') ?? 0,
                'nilai_terendah' => $jawabanDinilai->min('nilai') ?? 0,
                'persentase_pengumpulan' => 0,
            ];

            // Hitung persentase pengumpulan
            if ($kuisStats['total_siswa_kelas'] > 0) {
                $kuisStats['persentase_pengumpulan'] = round(
                    ($kuisStats['total_jawaban'] / $kuisStats['total_siswa_kelas']) * 100, 
                    2
                );
            }

            // Distribusi nilai
            $kuisStats['distribusi_nilai'] = $jawabanDinilai
                ->selectRaw("
                    CASE 
                        WHEN nilai >= 90 THEN 'A'
                        WHEN nilai >= 80 THEN 'B'
                        WHEN nilai >= 70 THEN 'C'
                        WHEN nilai >= 60 THEN 'D'
                        ELSE 'E'
                    END as grade,
                    COUNT(*) as jumlah
                ")
                ->groupBy('grade')
                ->pluck('jumlah', 'grade')
                ->toArray();
        }

        // Persentase kehadiran
        $persentaseKehadiran = 0;
        if ($absensiStats->total > 0) {
            $persentaseKehadiran = round(($absensiStats->hadir / $absensiStats->total) * 100, 2);
        }

        return view('guru.materi.show', compact(
            'materi', 
            'absensiStats', 
            'kuisStats',
            'persentaseKehadiran'
        ));
    }

    public function edit(Materi $materi)
    {
        $this->authorizeMateri($materi);
        
        $kelasList = range(1, 6);
        
        return view('guru.materi.edit', compact('materi', 'kelasList'));
    }

    public function update(Request $request, Materi $materi)
    {
        $this->authorizeMateri($materi);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:5000',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200',
            'is_published' => 'boolean',
            'tanggal_deadline' => 'nullable|date',
            'remove_file' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $materiData = [
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'is_published' => $request->boolean('is_published'),
            ];

            // Handle deadline
            if ($validated['tipe'] === 'kuis' && isset($validated['tanggal_deadline'])) {
                $materiData['tanggal_deadline'] = $validated['tanggal_deadline'];
            }

            // Handle file removal
            if ($request->boolean('remove_file') && $materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
                $materiData['file_path'] = null;
                $materiData['file_size'] = null;
                $materiData['file_type'] = null;
            }

            // Handle new file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($materi->file_path) {
                    Storage::disk('public')->delete($materi->file_path);
                }

                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $folder = $validated['tipe'] === 'kuis' ? 'materi/kuis' : 'materi/pembelajaran';
                $path = $file->storeAs($folder, $filename, 'public');
                
                $materiData['file_path'] = $path;
                $materiData['file_size'] = $file->getSize();
                $materiData['file_type'] = $file->getMimeType();
            }

            // Check if kelas changed and handle absensi
            $kelasChanged = $materi->kelas != $validated['kelas'];
            
            $materi->update($materiData);

            // If kelas changed and materi is published, recreate absensi
            if ($kelasChanged && $materi->is_published && $materi->tipe === 'materi') {
                // Delete old absensi
                $materi->absensi()->delete();
                // Create new absensi
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            Log::info("Materi updated by guru", [
                'guru_id' => Auth::id(),
                'materi_id' => $materi->id
            ]);

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to update materi", [
                'guru_id' => Auth::id(),
                'materi_id' => $materi->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'Gagal mengupdate materi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Materi $materi)
    {
        $this->authorizeMateri($materi);

        try {
            DB::beginTransaction();

            // Delete file if exists
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }

            // Delete related data (cascade akan handle ini jika sudah setup di migration)
            $materi->absensi()->delete();
            $materi->jawabanKuis()->delete();
            
            $materi->delete();

            DB::commit();

            Log::info("Materi deleted by guru", [
                'guru_id' => Auth::id(),
                'materi_id' => $materi->id
            ]);

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to delete materi", [
                'guru_id' => Auth::id(),
                'materi_id' => $materi->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus materi: ' . $e->getMessage());
        }
    }

    // ========================================
    // MATERI ACTIONS - IMPROVED
    // ========================================

    public function togglePublish(Materi $materi)
    {
        $this->authorizeMateri($materi);

        try {
            DB::beginTransaction();

            $newStatus = !$materi->is_published;
            $materi->update(['is_published' => $newStatus]);

            // Auto-create absensi saat publish pertama kali
            if ($newStatus && $materi->tipe === 'materi' && $materi->absensi()->count() === 0) {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            $status = $newStatus ? 'dipublish' : 'di-draft';
            
            return back()->with('success', "Materi berhasil $status!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function duplicate(Materi $materi)
    {
        $this->authorizeMateri($materi);

        try {
            DB::beginTransaction();

            $newMateri = $materi->replicate();
            $newMateri->judul = $materi->judul . ' (Copy)';
            $newMateri->is_published = false;
            
            // Handle file duplication
            if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
                $oldPath = $materi->file_path;
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = time() . '_' . uniqid() . '.' . $extension;
                $newPath = dirname($oldPath) . '/' . $newFilename;
                
                Storage::disk('public')->copy($oldPath, $newPath);
                $newMateri->file_path = $newPath;
            }
            
            $newMateri->save();

            DB::commit();

            return redirect()->route('guru.materi.edit', $newMateri)
                ->with('success', 'Materi berhasil diduplikasi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi materi: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:materi,id'
        ]);

        try {
            DB::beginTransaction();

            $materi = Materi::where('guru_id', Auth::id())
                ->whereIn('id', $validated['ids'])
                ->get();

            $deletedCount = 0;
            foreach ($materi as $m) {
                if ($m->file_path) {
                    Storage::disk('public')->delete($m->file_path);
                }
                $m->delete();
                $deletedCount++;
            }

            DB::commit();

            return back()->with('success', "$deletedCount materi berhasil dihapus!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus materi: ' . $e->getMessage());
        }
    }

    public function bulkPublish(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:materi,id',
            'action' => 'required|in:publish,unpublish'
        ]);

        try {
            DB::beginTransaction();

            $isPublished = $validated['action'] === 'publish';
            
            $updated = Materi::where('guru_id', Auth::id())
                ->whereIn('id', $validated['ids'])
                ->update(['is_published' => $isPublished]);

            // Create absensi for newly published materi
            if ($isPublished) {
                $materiList = Materi::where('guru_id', Auth::id())
                    ->whereIn('id', $validated['ids'])
                    ->where('tipe', 'materi')
                    ->get();

                foreach ($materiList as $m) {
                    if ($m->absensi()->count() === 0) {
                        $this->createAbsensiForMateri($m);
                    }
                }
            }

            DB::commit();

            $action = $isPublished ? 'dipublish' : 'di-draft';
            return back()->with('success', "$updated materi berhasil $action!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // ========================================
    // ABSENSI MANAGEMENT - IMPROVED
    // ========================================

    public function absensi(Materi $materi)
    {
        $this->authorizeMateri($materi);

        // Get all siswa in this kelas
        $siswa = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->with(['absensi' => fn($q) => $q->where('materi_id', $materi->id)])
            ->orderBy('name')
            ->get();

        // Statistik ringkas untuk header halaman absensi
        $summary = [
            'total_siswa' => $siswa->count(),
            'hadir' => $materi->absensi()->where('status', 'hadir')->count(),
            'izin' => $materi->absensi()->where('status', 'izin')->count(),
            'sakit' => $materi->absensi()->where('status', 'sakit')->count(),
            'alpha' => $materi->absensi()->where('status', 'alpha')->count(),
        ];

        return view('guru.absensi.index', compact('materi', 'siswa', 'summary'));
    }

    public function updateAbsensi(Request $request, Materi $materi)
    {
        $this->authorizeMateri($materi);

        $validated = $request->validate([
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:users,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['absensi'] as $data) {
                Absensi::updateOrCreate(
                    [
                        'materi_id' => $materi->id,
                        'siswa_id' => $data['siswa_id']
                    ],
                    [
                        'status' => $data['status'],
                        // Jika diubah guru manual, waktu akses bisa di-set sekarang atau dibiarkan
                        'waktu_akses' => Carbon::now() 
                    ]
                );
            }

            DB::commit();

            return back()->with('success', 'Data absensi berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    // ========================================
    // KUIS & PENILAIAN MANAGEMENT
    // ========================================

    public function hasilKuis(Materi $materi)
    {
        $this->authorizeMateri($materi);

        if ($materi->tipe !== 'kuis') {
            return redirect()->route('guru.materi.show', $materi)
                ->with('error', 'Materi ini bukan kuis.');
        }

        // Ambil semua siswa sekelas
        $siswa = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil jawaban yang sudah masuk
        $jawaban = JawabanKuis::where('materi_id', $materi->id)
            ->get()
            ->keyBy('siswa_id');

        // Statistik Nilai
        $stats = [
            'total_submit' => $jawaban->count(),
            'rata_rata' => $jawaban->whereNotNull('nilai')->avg('nilai'),
            'tertinggi' => $jawaban->max('nilai'),
            'terendah' => $jawaban->min('nilai'),
        ];

        return view('guru.kuis.hasil', compact('materi', 'siswa', 'jawaban', 'stats'));
    }

    public function detailJawaban(JawabanKuis $jawaban)
    {
        // Pastikan guru berhak melihat jawaban ini (melalui relasi materi)
        $this->authorizeMateri($jawaban->materi);

        return view('guru.kuis.detail', compact('jawaban'));
    }

    public function nilaiJawaban(Request $request, JawabanKuis $jawaban)
    {
        $this->authorizeMateri($jawaban->materi);

        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'komentar' => 'nullable|string|max:1000'
        ]);

        try {
            $jawaban->update([
                'nilai' => $validated['nilai'],
                'komentar' => $validated['komentar'],
                'dinilai_pada' => Carbon::now(),
                'dinilai_oleh' => Auth::id()
            ]);

            // Kirim notifikasi ke siswa (Opsional - jika ada sistem notifikasi)
            // Notification::send($jawaban->siswa, new KuisDinilai($jawaban));

            return redirect()->route('guru.kuis.hasil', $jawaban->materi_id)
                ->with('success', 'Nilai berhasil disimpan!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    // ========================================
    // HELPER METHODS (PRIVATE)
    // ========================================

    /**
     * Memastikan materi milik guru yang sedang login.
     * Jika tidak, throw 403 Forbidden.
     */
    private function authorizeMateri($materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }
    }

    /**
     * Membuat record absensi awal (default: alpha) untuk semua siswa di kelas
     * saat materi dipublish. Ini memudahkan tracking siapa yang belum buka.
     */
    private function createAbsensiForMateri(Materi $materi)
    {
        // Ambil semua siswa aktif di kelas target
        $siswaList = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->select('id')
            ->get();

        $absensiData = [];
        $now = Carbon::now();

        foreach ($siswaList as $siswa) {
            $absensiData[] = [
                'materi_id' => $materi->id,
                'siswa_id' => $siswa->id,
                'status' => 'alpha', // Default alpha sampai siswa membuka materi
                'waktu_akses' => null, // Belum akses
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert untuk performa (chunks jika siswa sangat banyak)
        if (!empty($absensiData)) {
            Absensi::insert($absensiData);
        }
    }

    // Method download/export rekap absensi (Opsional tapi komprehensif)
    public function exportRekapAbsensi(Request $request)
    {
        $guruId = Auth::id();
        $kelas = $request->query('kelas');
        $bulan = $request->query('bulan', Carbon::now()->month);
        $tahun = $request->query('tahun', Carbon::now()->year);

        // Logic export excel/pdf bisa ditaruh di sini
        // Menggunakan library seperti Maatwebsite Excel atau DomPDF
        
        return back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }

    private function getLoginCountThisMonth($guruId)
    {
        // PENTING: Laravel bawaan tidak menyimpan riwayat (history) setiap kali user login.
        // Jika kamu belum membuat tabel khusus (misal: 'login_logs'), 
        // kembalikan nilai 0 agar aplikasi tidak error.
        
        return 0; 
        
        // JIKA SUDAH PUNYA TABEL TRACKING, CONTOH KODENYA:
        /*
        return DB::table('authentication_log')
            ->where('authenticatable_id', $guruId)
            ->where('authenticatable_type', 'App\Models\User')
            ->whereMonth('login_at', Carbon::now()->month)
            ->count();
        */
    }
}