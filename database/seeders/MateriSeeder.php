<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// --- TAMBAHAN WAJIB (JANGAN DIHAPUS) ---
use App\Models\User;
use App\Models\Materi;
// ---------------------------------------

class MateriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Ambil data guru pertama untuk dijadikan pembuat materi
       $guru = User::where('role', 'guru')->first();
        
        // Jika belum ada guru, buat dummy guru (pencegah error)
        if (!$guru) {
            $guru = User::create([
                'name' => 'Guru Materi',
                'email' => 'guru.materi@sekolah.com',
                'password' => '$2y$12$KjGg.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0', // password dummy
                'role' => 'guru',
                'is_active' => true,
            ]);
        }

        $materiData = [
            [
                'judul' => 'Pengenalan Bahasa Indonesia',
                'keterangan' => 'Materi dasar tentang tata bahasa Indonesia untuk kelas 1',
                'tipe' => 'materi',
                'kelas' => '1',
                'is_published' => true,
                'tanggal_mulai' => now(), 
            ],
            [
                'judul' => 'Matematika Dasar - Penjumlahan',
                'keterangan' => 'Belajar penjumlahan bilangan 1-10',
                'tipe' => 'materi',
                'kelas' => '1',
                'is_published' => true,
                'tanggal_mulai' => now(), 
            ],
            [
                'judul' => 'Kuis Bahasa Indonesia Minggu 1',
                'keterangan' => 'Kuis untuk mengukur pemahaman materi Bahasa Indonesia',
                'tipe' => 'kuis',
                'kelas' => '1',
                'is_published' => true,
                'tanggal_mulai' => now(),
            ],
            [
                'judul' => 'IPA - Pengenalan Hewan',
                'keterangan' => 'Mengenal berbagai jenis hewan dan habitatnya',
                'tipe' => 'materi',
                'kelas' => '2',
                'is_published' => true,
                'tanggal_mulai' => now(),
            ],
            [
                'judul' => 'IPS - Kebudayaan Indonesia',
                'keterangan' => 'Mempelajari keberagaman budaya Indonesia',
                'tipe' => 'materi',
                'kelas' => '3',
                'is_published' => false,
                'tanggal_mulai' => now(),
            ],
        ];

        foreach ($materiData as $data) {
            $data['guru_id'] = $guru->id;
            Materi::create($data);
        }
    }
}