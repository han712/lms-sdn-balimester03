<?php

namespace App\Http\Controllers\Guru; // <--- WAJIB GURU

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        // $scriptPath = storage_path('app/python/predict.py');
        
        // if (!file_exists($scriptPath)) {
        //     return back()->with('error', 'Script Python tidak ditemukan di: ' . $scriptPath);
        // }

        // Call prediction API instead of executing Python locallyF
        $apiUrl = config('services.prediksi.url', env('PREDIKSI_API_URL', 'https://tika.gundar.id/predict'));

        try {
            // Prepare payload and send request
            $payload = [
                'rata_kuis' => $request->rata_nilai_kuis,
                'total_kuis' => $request->total_kuis_dikerjakan,
                'kehadiran' => $request->presentasi_kehadiran,
                'tidak_hadir' => $request->jumlah_tidak_hadir,
            ];

            $response = Http::timeout(10)->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'error') {
                    return back()->with('error', 'API Error: ' . ($data['message'] ?? 'Unknown'));
                }

                return back()->with('hasil', $data);
            }

            return back()->with('error', "API request failed: HTTP {$response->status()} - {$response->body()}");
        } catch (\Exception $e) {
            return back()->with('error', 'Request failed: ' . $e->getMessage());
        } finally {
            // Clear sensitive/temporary variables
            if (isset($payload)) {
                unset($payload);
            }
            if (isset($data)) {
                unset($data);
            }
            if (isset($response)) {
                unset($response);
            }
            if (isset($apiUrl)) {
                unset($apiUrl);
            }
        }
    }
}