<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\User;
use App\Models\JawabanKuis;
use Carbon\Carbon;

class MateriController extends Controller
{
    public function index()
    {
        $query = Materi::where('guru_id', Auth::id());

        // Search & Filters
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }
        if (request('kelas')) $query->where('kelas', request('kelas'));
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

        // Statistik header index
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:5000',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,png,mp4,avi|max:51200',
            'is_published' => 'boolean',
            'tanggal_deadline' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();
            
            $path = null;
            $fileSize = null;
            $fileType = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $folder = $validated['tipe'] === 'kuis' ? 'materi/kuis' : 'materi/pembelajaran';
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($folder, $filename, 'public');
                $fileSize = $file->getSize();
                $fileType = $file->getMimeType();
            }

            $materi = Materi::create([
                'guru_id' => Auth::id(),
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'file_path' => $path,
                'file_size' => $fileSize,
                'file_type' => $fileType, // Jika ada kolom ini di DB
                'is_published' => $request->boolean('is_published'),
                'tanggal_deadline' => $request->tanggal_deadline ?? null,
            ]);

            // Auto-create absensi jika langsung dipublish
            if ($materi->is_published && $materi->tipe === 'materi') {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();
            return redirect()->route('guru.materi.index')->with('success', 'Materi berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path)) Storage::disk('public')->delete($path);
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Materi $materi)
    {
        $this->authorizeMateri($materi);
        
        if ($materi->guru_id !== auth()->id() || $materi->tipe !== 'kuis') {
        abort(403);
        
        $materi->load(['jawabanKuis.siswa', 'jawabanKuis' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);}
        
        // Statistik Absensi
        $absensiStats = $materi->absensi()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->first();

        // Statistik Kuis (jika tipe kuis)
        $kuisStats = null;
        if ($materi->tipe === 'kuis') {
            $jawaban = $materi->jawabanKuis();
            $kuisStats = [
                'total_jawaban' => $jawaban->count(),
                'rata_rata' => round($jawaban->whereNotNull('nilai')->avg('nilai') ?? 0, 2),
            ];
        }

        $persentaseKehadiran = ($absensiStats->total > 0) 
            ? round(($absensiStats->hadir / $absensiStats->total) * 100, 2) 
            : 0;

        return view('guru.materi.show', compact('materi', 'absensiStats', 'kuisStats', 'persentaseKehadiran'));
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
        
        // Validasi dan Logic Update mirip store...
        // Singkatnya: Update data, handle file replacement, commit DB
        // ... (Kode update Anda sebelumnya sudah bagus, pindahkan kesini)
        
        // Note: Jika kelas berubah, reset absensi
        // if ($kelasChanged) $this->createAbsensiForMateri($materi);

        // Placeholder untuk mempersingkat jawaban
        $validated = $request->validate([
            'judul' => 'required',
            'kelas' => 'required',
            'tipe' => 'required'
        ]);
        
        $materi->update($request->except(['file']));
        // Handle file upload logic here...

        return redirect()->route('guru.materi.index')->with('success', 'Berhasil update');
    }

    public function destroy(Materi $materi)
    {
        $this->authorizeMateri($materi);
        if ($materi->file_path) Storage::disk('public')->delete($materi->file_path);
        $materi->delete();
        return redirect()->route('guru.materi.index')->with('success', 'Materi dihapus');
    }

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

    // --- Helper Private ---
    private function authorizeMateri($materi)
    {
        if ($materi->guru_id !== Auth::id()) abort(403);
    }

    private function createAbsensiForMateri(Materi $materi)
    {
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
                'status' => 'alpha',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        if (!empty($data)) Absensi::insert($data);
    }
}