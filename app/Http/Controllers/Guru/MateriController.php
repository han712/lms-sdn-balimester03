<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\User;

class MateriController extends Controller
{
    /**
     * Menampilkan daftar materi.
     */
    public function index()
    {
        $query = Materi::where('guru_id', Auth::id());

        // Search & Filters
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%"); // FIX: deskripsi -> keterangan
            });
        }
        
        if (request('kelas')) $query->where('kelas', request('kelas'));
        
        // Filter Tipe & Status
        if (request('tipe')) $query->where('tipe', request('tipe'));
        
        if (request('status')) {
            $isPublished = request('status') === 'published';
            $query->where('is_published', $isPublished);
        }

        $materi = $query->withCount([
                'absensi',
                'absensi as absensi_hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'jawabanKuis'
            ])
            ->latest()
            ->paginate(15)
            ->appends(request()->query());

        // Statistik untuk Header
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
        $kelasList = range(1, 6);
        return view('guru.materi.create', compact('kelasList'));
    }

    /**
     * Logic MENYIMPAN Materi Baru.
     */
    public function store(Request $request)
    {
        // FIX: Validasi disesuaikan dengan kolom database
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'required|string', // FIX: deskripsi -> keterangan
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png,mp4,avi|max:51200',
            'link' => 'nullable|url|max:500',
            'video' => 'nullable|url|max:500',
            'is_published' => 'boolean',
            // FIX: Tambahkan tanggal (Wajib di DB)
            'tanggal_mulai' => 'nullable|date', 
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();
        try {
            $path = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                // Simpan ke folder public/materi/pembelajaran
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('materi/pembelajaran', $filename, 'public');
            }

            // Create Data
            $materi = Materi::create([
                'guru_id' => Auth::id(),
                'judul' => $validated['judul'],
                'keterangan' => $validated['keterangan'], // FIX
                'tipe' => 'materi', // Paksa tipe 'materi' di controller ini
                'kelas' => $validated['kelas'],
                'file' => $path, // FIX: file_path -> file
                'link' => $request->link,
                'video' => $request->video,
                // FIX: Default tanggal_mulai = hari ini jika kosong, karena di DB not null
                'tanggal_mulai' => $request->tanggal_mulai ?? now(),
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_published' => $request->boolean('is_published'),
            ]);

            // Auto-create absensi jika langsung dipublish
            if ($materi->is_published) {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();
            return redirect()->route('guru.materi.index')->with('success', 'Materi berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file jika database gagal
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Materi $materi)
    {
        $this->authorizeMateri($materi);
        
        // Statistik Absensi Ringkas
        $absensiStats = $materi->absensi()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->first();

        $persentaseKehadiran = ($absensiStats->total > 0) 
            ? round(($absensiStats->hadir / $absensiStats->total) * 100, 2) 
            : 0;

        return view('guru.materi.show', compact('materi', 'absensiStats', 'persentaseKehadiran'));
    }

    public function edit(Materi $materi)
    {
        $this->authorizeMateri($materi);
        $kelasList = range(1, 6);
        return view('guru.materi.edit', compact('materi', 'kelasList'));
    }

    /**
     * Logic UPDATE Materi.
     */
    public function update(Request $request, Materi $materi)
    {
        $this->authorizeMateri($materi);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'required|string',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png,mp4,avi|max:51200',
            'link' => 'nullable|url|max:500',
            'video' => 'nullable|url|max:500',
            'is_published' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'judul' => $validated['judul'],
                'keterangan' => $validated['keterangan'],
                'kelas' => $validated['kelas'],
                'link' => $request->link,
                'video' => $request->video,
                'is_published' => $request->boolean('is_published'),
                'tanggal_mulai' => $request->tanggal_mulai ?? $materi->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ];

            // Handle File Upload Baru
            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                    Storage::disk('public')->delete($materi->file);
                }
                
                $file = $request->file('file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['file'] = $file->storeAs('materi/pembelajaran', $filename, 'public');
            }

            // Cek jika kelas berubah (untuk reset absensi)
            $kelasChanged = $materi->kelas != $validated['kelas'];
            
            $materi->update($data);

            // Jika kelas berubah dan materi published, reset absensi
            if ($kelasChanged && $materi->is_published) {
                $materi->absensi()->delete(); // Hapus absensi kelas lama
                $this->createAbsensiForMateri($materi); // Buat baru
            }

            DB::commit();
            return redirect()->route('guru.materi.index')->with('success', 'Materi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy(Materi $materi)
    {
        $this->authorizeMateri($materi);
        
        if ($materi->file && Storage::disk('public')->exists($materi->file)) {
            Storage::disk('public')->delete($materi->file);
        }
        
        $materi->delete(); // Soft delete jika trait enabled, atau force delete
        return redirect()->route('guru.materi.index')->with('success', 'Materi dihapus.');
    }

    // --- Actions Lainnya ---

    public function togglePublish(Materi $materi)
    {
        $this->authorizeMateri($materi);
        $newStatus = !$materi->is_published;
        
        $materi->update(['is_published' => $newStatus]);

        if ($newStatus && $materi->tipe === 'materi' && $materi->absensi()->count() === 0) {
            $this->createAbsensiForMateri($materi);
        }

        return back()->with('success', 'Status publish diubah.');
    }

    public function duplicate(Materi $materi)
    {
        $this->authorizeMateri($materi);
        
        $newMateri = $materi->replicate();
        $newMateri->judul = $materi->judul . ' (Copy)';
        $newMateri->is_published = false;
        
        // Copy file fisik jika ada
        if ($materi->file && Storage::disk('public')->exists($materi->file)) {
            $ext = pathinfo($materi->file, PATHINFO_EXTENSION);
            $newPath = 'materi/pembelajaran/' . time() . '_' . uniqid() . '.' . $ext;
            Storage::disk('public')->copy($materi->file, $newPath);
            $newMateri->file = $newPath;
        }

        $newMateri->save();

        return redirect()->route('guru.materi.index')->with('success', 'Materi diduplikasi sebagai draft.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) return back()->with('error', 'Tidak ada materi dipilih.');

        $materis = Materi::where('guru_id', Auth::id())->whereIn('id', $ids)->get();
        foreach ($materis as $m) {
            if ($m->file) Storage::disk('public')->delete($m->file);
            $m->delete();
        }

        return back()->with('success', count($materis) . ' materi dihapus.');
    }

    public function bulkPublish(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) return back()->with('error', 'Tidak ada materi dipilih.');
        
        $status = $request->action === 'publish';
        
        Materi::where('guru_id', Auth::id())
            ->whereIn('id', $ids)
            ->update(['is_published' => $status]);

        // Opsional: create absensi untuk yang baru dipublish (bisa ditambahkan logic di sini)

        return back()->with('success', 'Status materi diperbarui.');
    }

    // --- Helpers ---

    private function authorizeMateri($materi)
    {
        if ($materi->guru_id !== Auth::id()) abort(403);
    }

    private function createAbsensiForMateri(Materi $materi)
    {
        // Cari siswa di kelas tersebut
        $siswaList = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->pluck('id');

        $data = [];
        $now = now();
        foreach ($siswaList as $siswaId) {
            $data[] = [
                'materi_id' => $materi->id,
                'siswa_id' => $siswaId,
                'status' => 'alpha', // Default alpha
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        
        if (!empty($data)) Absensi::insert($data);
    }
}