<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Materi $materi)
    {
        // pastikan materi milik guru yang login
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // siswa sekelas & aktif
        $siswaList = User::where('role', 'siswa')
            ->where('kelas', $materi->kelas)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // ambil absensi existing untuk materi ini
        $absensiData = Absensi::where('materi_id', $materi->id)
            ->get()
            ->keyBy('siswa_id');

        // pastikan tiap siswa punya row absensi (default: tidak_hadir)
        DB::beginTransaction();
        try {
            $now = now();

            foreach ($siswaList as $s) {
                if (!$absensiData->has($s->id)) {
                    Absensi::create([
                        'materi_id'   => $materi->id,
                        'siswa_id'    => $s->id,
                        'status'      => 'tidak_hadir', // ✅ cocok dengan ENUM DB
                        'waktu_akses' => $now,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }

            // refresh data setelah insert
            $absensiData = Absensi::where('materi_id', $materi->id)
                ->get()
                ->keyBy('siswa_id');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyiapkan absensi: ' . $e->getMessage());
        }

        // ✅ view kamu pakai folder: resources/views/guru/absensi/index.blade.php
        return view('guru.absensi.index', compact('materi', 'siswaList', 'absensiData'));
    }

    public function update(Request $request, Materi $materi)
    {
        if ($materi->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // view kamu ngirim: absensi[<siswa_id>] = status
        $request->validate([
            'absensi'   => 'required|array',
            'absensi.*' => 'required|in:hadir,izin,sakit,tidak_hadir', // ✅ cocok enum DB
        ]);

        DB::beginTransaction();
        try {
            $now = now();

            foreach ($request->absensi as $siswaId => $status) {
                Absensi::updateOrCreate(
                    [
                        'materi_id' => $materi->id,
                        'siswa_id'  => $siswaId,
                    ],
                    [
                        'status'      => $status,
                        // waktu_akses biasanya diisi saat siswa membuka materi.
                        // tapi kalau guru edit manual, boleh update updated_at saja:
                        'updated_at'  => $now,
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Absensi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    public function exportRekap(Request $request)
    {
        return back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }
}