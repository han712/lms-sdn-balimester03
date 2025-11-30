<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\JawabanKuis;
use App\Models\User;

class GuruController extends Controller
{
    // ========================================
    // DASHBOARD
    // ========================================
    
    public function dashboard()
    {
        $guruId = Auth::id();

        // Statistik utama
        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_absensi' => Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))->count(),
            'total_jawaban' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))->count(),
            'jawaban_belum_dinilai' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))->whereNull('nilai')->count(),
        ];

        // Grafik materi per kelas
        $materi_per_kelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->toArray();

        // Kuis yang perlu dinilai
        $kuis_pending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
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
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
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

    // ========================================
    // MATERI CRUD
    // ========================================
    
    /**
     * Display listing of materi
     */
    public function index()
    {
        $materi = Materi::where('guru_id', Auth::id())
            ->when(request('search'), function($q) {
                $q->where('judul', 'like', '%' . request('search') . '%')
                  ->orWhere('deskripsi', 'like', '%' . request('search') . '%');
            })
            ->when(request('kelas'), function($q) {
                $q->where('kelas', request('kelas'));
            })
            ->when(request('tipe'), function($q) {
                $q->where('tipe', request('tipe'));
            })
            ->when(request('status'), function($q) {
                $is_published = request('status') === 'published';
                $q->where('is_published', $is_published);
            })
            ->latest()
            ->paginate(15);

        return view('guru.materi.index', compact('materi'));
    }

    /**
     * Show form for creating new materi
     */
    public function create()
    {
        return view('guru.materi.create');
    }

    /**
     * Store newly created materi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
            'is_published' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $materiData = [
                'guru_id' => Auth::id(),
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'is_published' => $request->has('is_published')
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('materi', $filename, 'public');
                $materiData['file_path'] = $path;
            }

            $materi = Materi::create($materiData);

            // Jika materi dipublish dan bukan kuis, auto-create absensi untuk siswa di kelas tersebut
            if ($materi->is_published && $materi->tipe === 'materi') {
                $siswa = User::where('role', 'siswa')
                    ->where('kelas', $materi->kelas)
                    ->where('is_active', true)
                    ->get();

                foreach ($siswa as $s) {
                    Absensi::create([
                        'siswa_id' => $s->id,
                        'materi_id' => $materi->id,
                        'status' => 'alpha', // Default alpha
                        'waktu_akses' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat materi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified materi
     */
    public function show(Materi $materi)
    {
        // Pastikan materi milik guru yang login
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $materi->load(['absensi.siswa', 'jawabanKuis.siswa']);

        // Statistik absensi
        $absensi_stats = [
            'hadir' => $materi->absensi()->where('status', 'hadir')->count(),
            'izin' => $materi->absensi()->where('status', 'izin')->count(),
            'sakit' => $materi->absensi()->where('status', 'sakit')->count(),
            'alpha' => $materi->absensi()->where('status', 'alpha')->count(),
        ];

        // Statistik kuis (jika kuis)
        $kuis_stats = null;
        if ($materi->tipe === 'kuis') {
            $jawaban = $materi->jawabanKuis()->whereNotNull('nilai');
            $kuis_stats = [
                'total_jawaban' => $materi->jawabanKuis()->count(),
                'sudah_dinilai' => $jawaban->count(),
                'belum_dinilai' => $materi->jawabanKuis()->whereNull('nilai')->count(),
                'rata_rata' => $jawaban->avg('nilai') ?? 0,
                'nilai_tertinggi' => $jawaban->max('nilai') ?? 0,
                'nilai_terendah' => $jawaban->min('nilai') ?? 0,
            ];
        }

        return view('guru.materi.show', compact('materi', 'absensi_stats', 'kuis_stats'));
    }

    /**
     * Show form for editing materi
     */
    public function edit(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('guru.materi.edit', compact('materi'));
    }

    /**
     * Update the specified materi
     */
    public function update(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
            'is_published' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $materiData = [
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'is_published' => $request->has('is_published')
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($materi->file_path) {
                    Storage::disk('public')->delete($materi->file_path);
                }

                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('materi', $filename, 'public');
                $materiData['file_path'] = $path;
            }

            $materi->update($materiData);

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate materi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified materi
     */
    public function destroy(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Delete file if exists
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }

            $materi->delete();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus materi: ' . $e->getMessage());
        }
    }

    // ========================================
    // MATERI ACTIONS
    // ========================================

    /**
     * Toggle publish status
     */
    public function togglePublish(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $materi->update([
            'is_published' => !$materi->is_published
        ]);

        $status = $materi->is_published ? 'dipublish' : 'di-draft';
        
        return back()->with('success', "Materi berhasil $status!");
    }

    /**
     * Duplicate materi
     */
    public function duplicate(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $newMateri = $materi->replicate();
        $newMateri->judul = $materi->judul . ' (Copy)';
        $newMateri->is_published = false;
        $newMateri->save();

        return redirect()->route('guru.materi.edit', $newMateri)
            ->with('success', 'Materi berhasil diduplikasi!');
    }

    /**
     * Bulk delete materi
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        $materi = Materi::where('guru_id', Auth::id())
            ->whereIn('id', $ids)
            ->get();

        foreach ($materi as $m) {
            if ($m->file_path) {
                Storage::disk('public')->delete($m->file_path);
            }
            $m->delete();
        }

        return back()->with('success', count($ids) . ' materi berhasil dihapus!');
    }

    // ========================================
    // ABSENSI MANAGEMENT
    // ========================================

    /**
     * Show absensi for specific materi
     */
    public function absensi(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $absensi = Absensi::with('siswa')
            ->where('materi_id', $materi->id)
            ->get()
            ->groupBy('siswa_id');

        // Get all siswa in this kelas
        $siswa = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->get();

        return view('guru.absensi.index', compact('materi', 'absensi', 'siswa'));
    }

    /**
     * Update single absensi
     */
    public function updateAbsensi(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string'
        ]);

        Absensi::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'materi_id' => $materi->id
            ],
            [
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'],
                'waktu_akses' => now()
            ]
        );

        return back()->with('success', 'Absensi berhasil diupdate!');
    }

    /**
     * Bulk update absensi
     */
    public function bulkUpdateAbsensi(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:users,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha',
            'absensi.*.keterangan' => 'nullable|string'
        ]);

        foreach ($validated['absensi'] as $item) {
            Absensi::updateOrCreate(
                [
                    'siswa_id' => $item['siswa_id'],
                    'materi_id' => $materi->id
                ],
                [
                    'status' => $item['status'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'waktu_akses' => now()
                ]
            );
        }

        return back()->with('success', 'Absensi berhasil diupdate secara bulk!');
    }

    /**
     * Export absensi
     */
    public function exportAbsensi(Request $request)
    {
        // Implementasi export ke Excel/CSV
        // Anda bisa gunakan Laravel Excel package
        
        return back()->with('info', 'Fitur export akan segera tersedia!');
    }

    // ========================================
    // KUIS & PENILAIAN
    // ========================================

    /**
     * Show jawaban kuis for specific materi
     */
    public function jawabanKuis(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($materi->tipe !== 'kuis') {
            return back()->with('error', 'Materi ini bukan kuis!');
        }

        $jawaban = JawabanKuis::with('siswa')
            ->where('materi_id', $materi->id)
            ->when(request('status'), function($q) {
                if (request('status') === 'belum_dinilai') {
                    $q->whereNull('nilai');
                } else if (request('status') === 'sudah_dinilai') {
                    $q->whereNotNull('nilai');
                }
            })
            ->latest()
            ->paginate(20);

        return view('guru.kuis.jawaban', compact('materi', 'jawaban'));
    }

    /**
     * Nilai single jawaban kuis
     */
    public function nilaiKuis(Request $request, JawabanKuis $jawaban)
    {
        // Pastikan jawaban ini untuk materi milik guru yang login
        if ($jawaban->materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string'
        ]);

        $jawaban->update([
            'nilai' => $validated['nilai'],
            'catatan_guru' => $validated['catatan_guru'],
            'dinilai_oleh' => Auth::id(),
            'dinilai_pada' => now()
        ]);

        return back()->with('success', 'Jawaban berhasil dinilai!');
    }

    /**
     * Bulk nilai kuis
     */
    public function bulkNilaiKuis(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'penilaian' => 'required|array',
            'penilaian.*.jawaban_id' => 'required|exists:jawaban_kuis,id',
            'penilaian.*.nilai' => 'required|integer|min:0|max:100',
            'penilaian.*.catatan_guru' => 'nullable|string'
        ]);

        foreach ($validated['penilaian'] as $item) {
            JawabanKuis::where('id', $item['jawaban_id'])
                ->where('materi_id', $materi->id)
                ->update([
                    'nilai' => $item['nilai'],
                    'catatan_guru' => $item['catatan_guru'] ?? null,
                    'dinilai_oleh' => Auth::id(),
                    'dinilai_pada' => now()
                ]);
        }

        return back()->with('success', 'Penilaian berhasil disimpan!');
    }
}