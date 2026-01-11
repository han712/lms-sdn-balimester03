<?php

namespace App\Http\Controllers\Guru; // <--- WAJIB GURU

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class PrediksiController extends Controller
{
    public function index()
    {
        // View ada di resources/views/guru/prediksi/index.blade.php
        return view('guru.prediksi.index');
    }

    public function predict(Request $request)
    {
        $request->validate([
            'rata_nilai_kuis' => 'required|numeric',
            'total_kuis_dikerjakan' => 'required|numeric',
            'presentasi_kehadiran' => 'required|numeric|between:0,100',
            'jumlah_tidak_hadir' => 'required|numeric',
        ]);

        // Pastikan path ini benar sesuai lokasi file Anda menaruh predict.py
        $scriptPath = storage_path('app/python/predict.py');
        
        if (!file_exists($scriptPath)) {
            return back()->with('error', 'Script Python tidak ditemukan di: ' . $scriptPath);
        }

        // Command Python
        $command = [
            'python', // ganti 'python3' jika di server linux/mac
            $scriptPath, 
            $request->rata_nilai_kuis, 
            $request->total_kuis_dikerjakan, 
            $request->presentasi_kehadiran, 
            $request->jumlah_tidak_hadir
        ];

        $result = Process::run($command);

        if ($result->successful()) {
            $output = $result->output();
            $data = json_decode($output, true);
            
            if (!$data) {
                return back()->with('error', 'Output tidak valid: ' . $output);
            }

            if (isset($data['status']) && $data['status'] == 'error') {
                return back()->with('error', 'Python Error: ' . $data['message']);
            }

            return back()->with('hasil', $data);
        } else {
            return back()->with('error', 'Gagal menjalankan Python: ' . $result->errorOutput());
        }
    }
}