<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserImportController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'role_default' => ['nullable', Rule::in(['guru', 'siswa'])], 
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        // 2. Baca Konten File
        $content = file_get_contents($path);
        
        // Hapus BOM (Byte Order Mark)
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // Normalisasi Baris Baru (\r\n -> \n)
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Hapus kutip ganda yang membungkus satu file penuh (jika ada)
        // Kadang text editor membungkus semua konten dengan " "
        $content = trim($content);
        
        $lines = explode("\n", $content);
        
        // Hapus baris kosong
        $lines = array_filter($lines, function($line) {
            return trim($line) !== '';
        });

        if (count($lines) < 2) {
            return back()->with('error', 'File CSV kosong atau hanya berisi header.');
        }

        // 3. Deteksi Delimiter (Comma vs Semicolon)
        $headerLine = reset($lines);
        $candidates = [',', ';', "\t", '|'];
        $delimiter = ',';
        $maxCols = 0;

        foreach ($candidates as $d) {
            // Cek jumlah kolom dengan delimiter ini
            $cols = count(str_getcsv($headerLine, $d));
            if ($cols > $maxCols) {
                $maxCols = $cols;
                $delimiter = $d;
            }
        }

        // --- FIX KHUSUS: Jika cuma terdeteksi 1 kolom tapi ada koma di dalamnya ---
        // Ini terjadi jika baris terbungkus kutip: "name,email,role" -> dianggap 1 kolom
        if ($maxCols === 1 && (str_contains($headerLine, ',') || str_contains($headerLine, ';'))) {
            // Coba paksa parse ulang isi kolom pertama
            $tempParse = str_getcsv($headerLine, $delimiter); // Ini akan membuang kutip luar
            if (isset($tempParse[0])) {
                // Cek lagi di dalamnya
                foreach ($candidates as $d) {
                    $innerCols = count(str_getcsv($tempParse[0], $d));
                    if ($innerCols > 1) {
                        $delimiter = $d; // Update delimiter
                        // Kita tandai bahwa kita perlu melakukan 'double parse' nanti
                        break;
                    }
                }
            }
        }

        // 4. Parse Semua Data
        $data = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line, $delimiter);
            
            // AUTO-FIX: Jika hasil parse cuma 1 kolom, coba parse lagi isinya
            // (Menangani kasus double-quoted line: "a,b,c")
            if (count($row) === 1 && isset($row[0]) && str_contains($row[0], ',')) {
                 $row = str_getcsv($row[0], ',');
            } elseif (count($row) === 1 && isset($row[0]) && str_contains($row[0], ';')) {
                 $row = str_getcsv($row[0], ';');
            }
            
            $data[] = $row;
        }

        // 5. Mapping Header
        $rawHeader = $data[0];
        unset($data[0]); // Buang header

        $header = array_map(function($h) {
            // Bersihkan nama header
            return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', trim($h)));
        }, $rawHeader);

        // Sinonim Kolom
        $columnMap = [
            'name' => ['name', 'nama', 'namalengkap', 'namauser', 'fullname'],
            'email' => ['email', 'surel', 'emailaddress', 'alamatemail'],
            'password' => ['password', 'katasandi', 'pass'],
            'role' => ['role', 'peran', 'tipe', 'jabatan'],
            'nisn' => ['nisn', 'nomorinduksiswanasional'],
            'nip' => ['nip', 'nomorindukpegawai'],
            'kelas' => ['kelas', 'rombel', 'grade'],
        ];

        $mappedHeader = [];
        foreach ($header as $index => $h) {
            foreach ($columnMap as $dbCol => $csvAliases) {
                if (in_array($h, $csvAliases)) {
                    $mappedHeader[$dbCol] = $index;
                    break; // Pindah ke header berikutnya
                }
            }
        }

        // 6. Validasi Kolom Wajib
        if (!isset($mappedHeader['name']) || !isset($mappedHeader['email'])) {
            $debugHeader = implode(' | ', $rawHeader);
            return back()->with('error', 
                "Gagal mengenali format. Header terbaca: [ {$debugHeader} ]. " . 
                "Pastikan format CSV benar (pisahkan dengan koma atau titik koma)."
            );
        }

        // 7. Simpan ke Database
        DB::beginTransaction();
        
        $successCount = 0;
        $errors = [];

        foreach ($data as $rowIndex => $rowData) {
            // Skip baris kosong/rusak
            if (count($rowData) < 2) continue;

            // Helper untuk ambil data berdasarkan mapping
            $getValue = function($key) use ($mappedHeader, $rowData) {
                return isset($mappedHeader[$key], $rowData[$mappedHeader[$key]]) 
                       ? trim($rowData[$mappedHeader[$key]]) 
                       : null;
            };

            $role = $getValue('role') ? strtolower($getValue('role')) : $request->role_default;
            $name = $getValue('name');
            $email = $getValue('email');
            
            // Validasi Data Row
            if (!$name || !$email) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": Nama atau Email kosong.";
                continue;
            }

            if (!in_array($role, ['guru', 'siswa', 'admin'])) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": Role tidak valid.";
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": Email {$email} sudah ada.";
                continue;
            }
            // 1. Cek Duplikat NIP (Khusus Guru)
            $nip = ($role === 'guru') ? $getValue('nip') : null;
            if ($nip && User::where('nip', $nip)->exists()) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": NIP '{$nip}' sudah digunakan user lain.";
                continue;
            }

            // 2. Cek Duplikat NISN (Khusus Siswa)
            $nisn = ($role === 'siswa') ? $getValue('nisn') : null;
            if ($nisn && User::where('nisn', $nisn)->exists()) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": NISN '{$nisn}' sudah digunakan user lain.";
                continue;
            }
            try {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($getValue('password') ?: 'password123'),
                    'role' => $role,
                    'nisn' => ($role === 'siswa') ? $getValue('nisn') : null,
                    'nip' => ($role === 'guru') ? $getValue('nip') : null,
                    'kelas' => ($role === 'siswa') ? $getValue('kelas') : null,
                    'is_active' => true,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris " . ($rowIndex + 1) . ": Error DB - " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', "Berhasil import {$successCount} user.")
                ->with('import_errors', $errors);
        } else {
            DB::rollBack();
            return back()->with('error', 'Gagal import.')->with('import_errors', $errors);
        }
    }
}