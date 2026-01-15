<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $query = Materi::where('guru_id', Auth::id());

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Filter kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Filter tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter status publish
        if ($request->filled('status')) {
            $isPublished = $request->status === 'published';
            $query->where('is_published', $isPublished);
        }

        $materi = $query->latest()->paginate(15)->appends($request->query());

        $filterStats = [
            'total'     => Materi::where('guru_id', Auth::id())->count(),
            'published' => Materi::where('guru_id', Auth::id())->where('is_published', true)->count(),
            'draft'     => Materi::where('guru_id', Auth::id())->where('is_published', false)->count(),
            'materi'    => Materi::where('guru_id', Auth::id())->where('tipe', 'materi')->count(),
            'kuis'      => Materi::where('guru_id', Auth::id())->where('tipe', 'kuis')->count(),
        ];

        return view('guru.materi.index', compact('materi', 'filterStats'));
    }

    public function create()
    {
        // pakai config biar konsisten dengan view
        $kelasList = config('lms.daftar_kelas', range(1, 6));
        return view('guru.materi.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $kelasValid = config('lms.daftar_kelas', range(1, 6));

        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'keterangan'      => 'required|string',
            'tipe'            => 'required|in:materi,kuis',
            'kelas'           => ['required', Rule::in($kelasValid)],

            'file'            => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200',
            'link'            => 'nullable|url|max:500',
            'video'           => 'nullable|url|max:500',

            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'is_published'    => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $path = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('materi/pembelajaran', $filename, 'public');
            }

            $materi = Materi::create([
                'guru_id'         => Auth::id(),
                'judul'           => $validated['judul'],
                'keterangan'      => $validated['keterangan'],
                'tipe'            => $validated['tipe'], // dari form
                'kelas'           => $validated['kelas'],
                'file'            => $path,              // kalau kolommu file_path, ganti di sini
                'link'            => $validated['link'] ?? null,
                'video'           => $validated['video'] ?? null,
                'tanggal_mulai'   => $validated['tanggal_mulai'] ?? now(),
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'is_published'    => $request->boolean('is_published'),
            ]);

            // Auto create absensi jika dipublish & tipe materi
            if ($materi->is_published && $materi->tipe === 'materi') {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            return redirect()
                ->route('guru.materi.index')
                ->with('success', 'Materi berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            // hapus file jika sudah terlanjur upload
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Materi $materi)
    {
      $this->authorizeMateri($materi);

    // load relasi kalau view butuh
    $materi->load([
        'guru',
        'absensi.siswa',
        'jawabanKuis.siswa',
    ]);

    // Statistik absensi
    $absensiStats = $materi->absensi()
        ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir
        ")
        ->first();

    $persentaseKehadiran = 0;
    if (($absensiStats->total ?? 0) > 0) {
        $persentaseKehadiran = round((($absensiStats->hadir ?? 0) / $absensiStats->total) * 100, 2);
    }

    // Statistik kuis (kalau tipe = kuis)
    $kuisStats = null;
    if ($materi->tipe === 'kuis') {
        $jawaban = $materi->jawabanKuis;
        $dinilai = $jawaban->whereNotNull('nilai');

        $kuisStats = [
            'total_jawaban'   => $jawaban->count(),
            'sudah_dinilai'   => $dinilai->count(),
            'belum_dinilai'   => $jawaban->whereNull('nilai')->count(),
            'rata_rata'       => round($dinilai->avg('nilai') ?? 0, 2),
            'nilai_tertinggi' => $dinilai->max('nilai') ?? 0,
            'nilai_terendah'  => $dinilai->min('nilai') ?? 0,
        ];
    }

    return view('guru.materi.show', compact(
        'materi',
        'absensiStats',
        'persentaseKehadiran',
        'kuisStats'
    ));
}

    public function edit(Materi $materi)
    {
        $this->authorizeMateri($materi);

        $kelasList = config('lms.daftar_kelas', range(1, 6));
        return view('guru.materi.edit', compact('materi', 'kelasList'));
    }

    public function update(Request $request, Materi $materi)
    {
        $this->authorizeMateri($materi);

        $kelasValid = config('lms.daftar_kelas', range(1, 6));

        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'keterangan'      => 'required|string',
            'tipe'            => 'required|in:materi,kuis',
            'kelas'           => ['required', Rule::in($kelasValid)],

            'file'            => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200',
            'link'            => 'nullable|url|max:500',
            'video'           => 'nullable|url|max:500',

            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',

            'is_published'    => 'nullable|boolean',
            'remove_file'     => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'judul'           => $validated['judul'],
                'keterangan'      => $validated['keterangan'],
                'tipe'            => $validated['tipe'],
                'kelas'           => $validated['kelas'],
                'link'            => $validated['link'] ?? null,
                'video'           => $validated['video'] ?? null,
                'tanggal_mulai'   => $validated['tanggal_mulai'] ?? $materi->tanggal_mulai,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'is_published'    => $request->boolean('is_published'),
            ];

            $kelasChanged = $materi->kelas != $validated['kelas'];

            // remove file
            if ($request->boolean('remove_file') && $materi->file) {
                if (Storage::disk('public')->exists($materi->file)) {
                    Storage::disk('public')->delete($materi->file);
                }
                $data['file'] = null;
            }

            // upload file baru
            if ($request->hasFile('file')) {
                if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                    Storage::disk('public')->delete($materi->file);
                }

                $file = $request->file('file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $data['file'] = $file->storeAs('materi/pembelajaran', $filename, 'public');
            }

            $materi->update($data);

            // reset absensi kalau kelas berubah dan materi published
            if ($kelasChanged && $materi->is_published && $materi->tipe === 'materi') {
                $materi->absensi()->delete();
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            return redirect()
                ->route('guru.materi.index')
                ->with('success', 'Materi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Materi $materi)
    {
        $this->authorizeMateri($materi);

        DB::beginTransaction();
        try {
            if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                Storage::disk('public')->delete($materi->file);
            }

            $materi->absensi()->delete();
            $materi->delete();

            DB::commit();

            return redirect()->route('guru.materi.index')->with('success', 'Materi dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    public function togglePublish(Materi $materi)
    {
        $this->authorizeMateri($materi);

        DB::beginTransaction();
        try {
            $newStatus = !$materi->is_published;
            $materi->update(['is_published' => $newStatus]);

            if ($newStatus && $materi->tipe === 'materi' && $materi->absensi()->count() === 0) {
                $this->createAbsensiForMateri($materi);
            }

            DB::commit();

            return back()->with('success', 'Status publish diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function duplicate(Materi $materi)
    {
        $this->authorizeMateri($materi);

        DB::beginTransaction();
        try {
            $newMateri = $materi->replicate();
            $newMateri->judul = $materi->judul . ' (Copy)';
            $newMateri->is_published = false;

            if ($materi->file && Storage::disk('public')->exists($materi->file)) {
                $ext = pathinfo($materi->file, PATHINFO_EXTENSION);
                $newPath = 'materi/pembelajaran/' . time() . '_' . uniqid() . '.' . $ext;
                Storage::disk('public')->copy($materi->file, $newPath);
                $newMateri->file = $newPath;
            }

            $newMateri->save();

            DB::commit();

            return redirect()->route('guru.materi.edit', $newMateri->id)
                ->with('success', 'Materi berhasil diduplikasi sebagai draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal duplikat: ' . $e->getMessage());
        }
    }

    // =========================
    // Helpers
    // =========================
    private function authorizeMateri(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function createAbsensiForMateri(Materi $materi)
    {
        $siswaIds = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->pluck('id');

        $now = now();
        $rows = [];

        foreach ($siswaIds as $sid) {
            $rows[] = [
                'materi_id'   => $materi->id,
                'siswa_id'    => $sid,
                'status'      => 'tidak_hadir', // konsisten dengan code lain
                'waktu_akses' => $now,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        if (!empty($rows)) {
            Absensi::insert($rows);
        }
    }
}