<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Materi::with(['guru'])
            ->where('guru_id', auth()->id());

        // Filter by tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Filter by status published
        if ($request->filled('published')) {
            $query->where('is_published', $request->published === '1');
        }

        // Search
        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $materi = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Materi::where('guru_id', auth()->id())->count(),
            'published' => Materi::where('guru_id', auth()->id())->where('is_published', true)->count(),
            'draft' => Materi::where('guru_id', auth()->id())->where('is_published', false)->count(),
            'materi' => Materi::where('guru_id', auth()->id())->where('tipe', 'materi')->count(),
            'kuis' => Materi::where('guru_id', auth()->id())->where('tipe', 'kuis')->count(),
        ];

        return view('guru.materi.index', compact('materi', 'stats'));
    }

    /**
     * Show the form for creating new materi.
     */
    public function create()
    {
        return view('guru.materi.create');
    }

    /**
     * Store newly created materi.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'tipe' => ['required', Rule::in(['materi', 'kuis'])],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip', 'max:10240'],
            'link' => ['nullable', 'url', 'max:500'],
            'video' => ['nullable', 'url', 'max:500'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'kelas' => ['required', Rule::in(['1', '2', '3', '4', '5', '6'])],
            'is_published' => ['nullable', 'boolean'],
        ], [
            'judul.required' => 'Judul materi wajib diisi',
            'tipe.required' => 'Tipe materi wajib dipilih',
            'file.max' => 'Ukuran file maksimal 10MB',
            'file.mimes' => 'Format file: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
            'kelas.required' => 'Kelas wajib dipilih',
            'link.url' => 'Format URL link tidak valid',
            'video.url' => 'Format URL video tidak valid',
        ]);

        DB::beginTransaction();
        
        try {
            $validated['guru_id'] = auth()->id();
            $validated['is_published'] = $request->has('is_published');

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $validated['file'] = $file->storeAs('materi', $filename, 'public');
            }

            $materi = Materi::create($validated);

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil ditambahkan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($validated['file'])) {
                Storage::disk('public')->delete($validated['file']);
            }
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan materi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified materi.
     */
    public function show(Materi $materi)
    {
        $this->authorize('view', $materi);

        $materi->load(['guru', 'absensi.siswa', 'jawabanKuis.siswa']);

        // Statistics untuk materi ini
        $stats = [
            'total_siswa' => User::siswa()->where('kelas', $materi->kelas)->where('is_active', true)->count(),
            'hadir' => $materi->absensi()->where('status', 'hadir')->count(),
            'tidak_hadir' => $materi->absensi()->where('status', 'tidak_hadir')->count(),
            'sakit' => $materi->absensi()->where('status', 'sakit')->count(),
            'izin' => $materi->absensi()->where('status', 'izin')->count(),
        ];

        if ($materi->tipe === 'kuis') {
            $stats['total_jawaban'] = $materi->jawabanKuis()->count();
            $stats['sudah_dinilai'] = $materi->jawabanKuis()->sudahDinilai()->count();
            $stats['belum_dinilai'] = $materi->jawabanKuis()->belumDinilai()->count();
            $stats['rata_nilai'] = $materi->jawabanKuis()->sudahDinilai()->avg('nilai');
        }

        return view('guru.materi.show', compact('materi', 'stats'));
    }

    /**
     * Show the form for editing materi.
     */
    public function edit(Materi $materi)
    {
        $this->authorize('update', $materi);

        return view('guru.materi.edit', compact('materi'));
    }

    /**
     * Update the specified materi.
     */
    public function update(Request $request, Materi $materi)
    {
        $this->authorize('update', $materi);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'tipe' => ['required', Rule::in(['materi', 'kuis'])],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip', 'max:10240'],
            'link' => ['nullable', 'url', 'max:500'],
            'video' => ['nullable', 'url', 'max:500'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'kelas' => ['required', Rule::in(['1', '2', '3', '4', '5', '6'])],
            'is_published' => ['nullable', 'boolean'],
            'hapus_file' => ['nullable', 'boolean'], // Checkbox untuk hapus file
        ], [
            'judul.required' => 'Judul materi wajib diisi',
            'tipe.required' => 'Tipe materi wajib dipilih',
            'file.max' => 'Ukuran file maksimal 10MB',
            'file.mimes' => 'Format file: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
            'kelas.required' => 'Kelas wajib dipilih',
        ]);

        DB::beginTransaction();
        
        try {
            $validated['is_published'] = $request->has('is_published');

            // Handle hapus file existing
            if ($request->has('hapus_file') && $materi->file) {
                Storage::disk('public')->delete($materi->file);
                $validated['file'] = null;
            }

            // Handle file upload baru
            if ($request->hasFile('file')) {
                // Delete old file
                if ($materi->file) {
                    Storage::disk('public')->delete($materi->file);
                }
                
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $validated['file'] = $file->storeAs('materi', $filename, 'public');
            }

            $materi->update($validated);

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil diupdate');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if (isset($validated['file']) && $validated['file'] !== null) {
                Storage::disk('public')->delete($validated['file']);
            }
            
            return back()->withInput()
                ->with('error', 'Gagal mengupdate materi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified materi.
     */
    public function destroy(Materi $materi)
    {
        $this->authorize('delete', $materi);

        DB::beginTransaction();
        
        try {
            $judul = $materi->judul;
            
            // Delete file if exists
            if ($materi->file) {
                Storage::disk('public')->delete($materi->file);
            }

            // Delete materi (cascade akan delete absensi & jawaban)
            $materi->delete();

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', "Materi '{$judul}' berhasil dihapus");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Gagal menghapus materi: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete materi.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'materi_ids' => ['required', 'array'],
            'materi_ids.*' => ['exists:materi,id'],
        ]);

        DB::beginTransaction();
        
        try {
            $materiList = Materi::whereIn('id', $request->materi_ids)
                ->where('guru_id', auth()->id())
                ->get();

            foreach ($materiList as $materi) {
                if ($materi->file) {
                    Storage::disk('public')->delete($materi->file);
                }
                $materi->delete();
            }

            DB::commit();

            return back()->with('success', count($materiList) . ' materi berhasil dihapus');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus materi: ' . $e->getMessage());
        }
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(Materi $materi)
    {
        $this->authorize('update', $materi);

        $materi->update(['is_published' => !$materi->is_published]);

        $status = $materi->is_published ? 'dipublish' : 'disembunyikan';
        
        return back()->with('success', "Materi berhasil {$status}");
    }

    /**
     * Duplicate materi.
     */
    public function duplicate(Materi $materi)
    {
        $this->authorize('view', $materi);

        DB::beginTransaction();
        
        try {
            $newMateri = $materi->replicate();
            $newMateri->judul = $materi->judul . ' (Copy)';
            $newMateri->is_published = false;
            $newMateri->tanggal_mulai = now();
            $newMateri->tanggal_selesai = null;
            
            // Copy file if exists
            if ($materi->file) {
                $oldPath = $materi->file;
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = time() . '_copy_' . basename($oldPath);
                $newPath = 'materi/' . $newFilename;
                
                Storage::disk('public')->copy($oldPath, $newPath);
                $newMateri->file = $newPath;
            }
            
            $newMateri->save();

            DB::commit();

            return redirect()->route('guru.materi.edit', $newMateri)
                ->with('success', 'Materi berhasil diduplikasi. Silakan edit sesuai kebutuhan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi materi: ' . $e->getMessage());
        }
    }

    /**
     * Display absensi for specific materi.
     */
    public function absensi(Materi $materi)
    {
        $this->authorize('view', $materi);

        $absensi = Absensi::with('siswa')
            ->where('materi_id', $materi->id)
            ->orderBy('waktu_akses', 'desc')
            ->get();

        // Get all siswa in this kelas
        $allSiswa = User::siswa()
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Mark siswa yang belum absen
        $siswaYangBelumAbsen = $allSiswa->filter(function($siswa) use ($absensi) {
            return !$absensi->contains('siswa_id', $siswa->id);
        });

        return view('guru.materi.absensi', compact('materi', 'absensi', 'allSiswa', 'siswaYangBelumAbsen'));
    }

    /**
     * Update absensi status manually.
     */
    public function updateAbsensi(Request $request, Materi $materi)
    {
        $this->authorize('view', $materi);

        $validated = $request->validate([
            'siswa_id' => ['required', 'exists:users,id'],
            'status' => ['required', Rule::in(['hadir', 'tidak_hadir', 'sakit', 'izin'])],
        ]);

        // Verify siswa is in correct class
        $siswa = User::findOrFail($validated['siswa_id']);
        if ($siswa->kelas != $materi->kelas) {
            return back()->with('error', 'Siswa bukan dari kelas ini');
        }

        Absensi::updateOrCreate(
            [
                'materi_id' => $materi->id,
                'siswa_id' => $validated['siswa_id'],
            ],
            [
                'status' => $validated['status'],
                'waktu_akses' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        return back()->with('success', 'Absensi berhasil diupdate');
    }

    /**
     * Bulk update absensi.
     */
    public function bulkUpdateAbsensi(Request $request, Materi $materi)
    {
        $this->authorize('view', $materi);

        $validated = $request->validate([
            'siswa_ids' => ['required', 'array'],
            'siswa_ids.*' => ['exists:users,id'],
            'status' => ['required', Rule::in(['hadir', 'tidak_hadir', 'sakit', 'izin'])],
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($validated['siswa_ids'] as $siswaId) {
                Absensi::updateOrCreate(
                    [
                        'materi_id' => $materi->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'status' => $validated['status'],
                        'waktu_akses' => now(),
                    ]
                );
            }

            DB::commit();

            return back()->with('success', count($validated['siswa_ids']) . ' absensi berhasil diupdate');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate absensi: ' . $e->getMessage());
        }
    }

    /**
     * Export absensi to Excel.
     */
    public function exportAbsensi(Request $request)
    {
        $validated = $request->validate([
            'periode' => ['required', Rule::in(['semester1', 'semester2', 'tahunan'])],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
            'kelas' => ['nullable', Rule::in(['1', '2', '3', '4', '5', '6'])],
        ]);

        $query = Absensi::with(['siswa', 'materi'])
            ->whereHas('materi', function($q) {
                $q->where('guru_id', auth()->id());
            });

        // Filter by periode
        if ($validated['periode'] === 'semester1') {
            $query->semester1($validated['tahun']);
        } elseif ($validated['periode'] === 'semester2') {
            $query->semester2($validated['tahun']);
        } else {
            $query->tahunan($validated['tahun']);
        }

        // Filter by kelas if specified
        if ($request->filled('kelas')) {
            $query->byKelas($validated['kelas']);
        }

        $absensi = $query->orderBy('created_at', 'desc')->get();

        return $this->downloadExcel($absensi, $validated);
    }

    /**
     * Generate Excel file.
     */
    private function downloadExcel($data, $params)
    {
        $filename = "absensi_{$params['periode']}_{$params['tahun']}";
        if (isset($params['kelas'])) {
            $filename .= "_kelas{$params['kelas']}";
        }
        $filename .= "_" . now()->format('YmdHis') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'NISN',
                'Nama Siswa',
                'Kelas',
                'Materi/Kuis',
                'Tipe',
                'Status',
                'Waktu Akses',
                'Durasi (menit)',
                'IP Address',
            ]);

            // Data
            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->siswa->nisn ?? '-',
                    $row->siswa->name,
                    $row->siswa->kelas,
                    $row->materi->judul,
                    ucfirst($row->materi->tipe),
                    $row->status_name,
                    $row->waktu_akses ? $row->waktu_akses->format('d/m/Y H:i:s') : '-',
                    $row->durasi_akses ?? '-',
                    $row->ip_address ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display jawaban kuis.
     */
    public function jawabanKuis(Materi $materi)
    {
        $this->authorize('view', $materi);

        if ($materi->tipe !== 'kuis') {
            return back()->with('error', 'Ini bukan materi kuis');
        }

        $jawaban = JawabanKuis::with('siswa')
            ->where('materi_id', $materi->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get siswa yang belum mengerjakan
        $allSiswa = User::siswa()
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->get();

        $siswaYangBelumMengerjakan = $allSiswa->filter(function($siswa) use ($jawaban) {
            return !$jawaban->contains('siswa_id', $siswa->id);
        });

        return view('guru.materi.jawaban-kuis', compact('materi', 'jawaban', 'siswaYangBelumMengerjakan'));
    }

    /**
     * Nilai jawaban kuis.
     */
    public function nilaiKuis(Request $request, JawabanKuis $jawaban)
    {
        $validated = $request->validate([
            'nilai' => ['required', 'integer', 'min:0', 'max:100'],
            'catatan_guru' => ['nullable', 'string', 'max:1000'],
        ], [
            'nilai.required' => 'Nilai wajib diisi',
            'nilai.integer' => 'Nilai harus berupa angka',
            'nilai.min' => 'Nilai minimal 0',
            'nilai.max' => 'Nilai maksimal 100',
        ]);

        $validated['dinilai_pada'] = now();
        $validated['dinilai_oleh'] = auth()->id();

        $jawaban->update($validated);

        return back()->with('success', 'Jawaban berhasil dinilai dengan nilai ' . $validated['nilai']);
    }

    /**
     * Bulk nilai kuis.
     */
    public function bulkNilaiKuis(Request $request, Materi $materi)
    {
        $this->authorize('view', $materi);

        $validated = $request->validate([
            'jawaban_ids' => ['required', 'array'],
            'jawaban_ids.*' => ['exists:jawaban_kuis,id'],
            'nilai' => ['required', 'integer', 'min:0', 'max:100'],
            'catatan_guru' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        
        try {
            JawabanKuis::whereIn('id', $validated['jawaban_ids'])
                ->where('materi_id', $materi->id)
                ->update([
                    'nilai' => $validated['nilai'],
                    'catatan_guru' => $validated['catatan_guru'] ?? null,
                    'dinilai_pada' => now(),
                    'dinilai_oleh' => auth()->id(),
                ]);

            DB::commit();

            return back()->with('success', count($validated['jawaban_ids']) . ' jawaban berhasil dinilai');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menilai jawaban: ' . $e->getMessage());
        }
    }
}
