<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Materi;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) abort(403);

        $siswa = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->with(['absensi' => fn($q) => $q->where('materi_id', $materi->id)])
            ->orderBy('name')
            ->get();

        $summary = [
            'total_siswa' => $siswa->count(),
            'hadir' => $materi->absensi()->where('status', 'hadir')->count(),
            'izin' => $materi->absensi()->where('status', 'izin')->count(),
            'sakit' => $materi->absensi()->where('status', 'sakit')->count(),
            'alpha' => $materi->absensi()->where('status', 'alpha')->count(),
        ];

        return view('guru.absensi.index', compact('materi', 'siswa', 'summary'));
    }

    public function update(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:users,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        foreach ($validated['absensi'] as $data) {
            Absensi::updateOrCreate(
                ['materi_id' => $materi->id, 'siswa_id' => $data['siswa_id']],
                ['status' => $data['status'], 'waktu_akses' => Carbon::now()]
            );
        }

        return back()->with('success', 'Absensi diperbarui.');
    }
    
    public function exportRekap(Request $request)
    {
        // Logic export excel bisa disini
        return back()->with('info', 'Fitur export coming soon.');
    }
}