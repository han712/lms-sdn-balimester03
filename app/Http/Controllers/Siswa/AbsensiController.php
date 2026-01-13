<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    /**
     * Halaman Riwayat Absensi
     */
    public function index()
    {
        $riwayat = Absensi::with('materi')
            ->where('siswa_id', Auth::id())
            ->latest('waktu_akses')
            ->paginate(15);

        return view('siswa.riwayat.absensi', compact('riwayat'));
    }
}