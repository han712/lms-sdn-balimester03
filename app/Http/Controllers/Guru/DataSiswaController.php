<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class DataSiswaController extends Controller
{
    public function index()
    {
        // Baca file JSON langsung
        $json = Storage::disk('local')->get('siswa_full.json');
        $data = json_decode($json, true);

        return view('guru.datasiswa', [
            'data' => $data
        ]);
    }
}