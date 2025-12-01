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
     * ======================================
     * DASHBOARD GURU
     * ======================================
     */
    public function index()
    {
        $guruId = Auth::id();

        $stats = [
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'published_materi' => Materi::where('guru_id', $guruId)->where('is_published', true)->count(),
            'draft_materi' => Materi::where('guru_id', $guruId)->where('is_published', false)->count(),
            'total_kuis' => Materi::where('guru_id', $guruId)->where('tipe', 'kuis')->count(),
            'total_absensi' => Absensi::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))->count(),
            'total_jawaban' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))->count(),
            'jawaban_belum_dinilai' => JawabanKuis::whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
                ->whereNull('nilai')->count(),
        ];

        $materi_per_kelas = Materi::where('guru_id', $guruId)
            ->select('kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->toArray();

        $kuis_pending = JawabanKuis::with(['siswa', 'materi'])
            ->whereHas('materi', fn($q) => $q->where('guru_id', $guruId))
            ->whereNull('nilai')
            ->latest()
            ->take(5)
            ->get();

        $recent_materi = Materi::where('guru_id', $guruId)
            ->latest()
            ->take(5)
            ->get();

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

        return view('guru.materi.index', compact('materi'));
    }

    public function createMateri()
    {
        return view('guru.materi.create');
    }

    public function storeMateri(Request $request)
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

            $data = [
                'guru_id' => Auth::id(),
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'kelas' => $validated['kelas'],
                'is_published' => $request->has('is_published')
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('materi', $filename, 'public');
                $data['file_path'] = $path;
            }

            $materi = Materi::create($data);

            if ($materi->is_published && $materi->tipe === 'materi') {
                $siswa = User::where('role', 'siswa')
                    ->where('kelas', $materi->kelas)
                    ->where('is_active', true)
                    ->get();

                foreach ($siswa as $s) {
                    Absensi::create([
                        'siswa_id' => $s->id,
                        'materi_id' => $materi->id,
                        'status' => 'alpha',
                        'waktu_akses' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('guru.materi.index')
                ->with('success', 'Materi berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}