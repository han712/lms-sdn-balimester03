<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MateriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $guru = User::where('role', 'guru')->first();
        
        if (!$guru) {
            $this->command->error('No guru found. Run GuruSeeder first.');
            return;
        }

        $materiData = [
            [
                'judul' => 'Pengenalan Bahasa Indonesia',
                'deskripsi' => 'Materi dasar tentang tata bahasa Indonesia untuk kelas 1',
                'tipe' => 'materi',
                'kelas' => '1',
                'is_published' => true,
            ],
            [
                'judul' => 'Matematika Dasar - Penjumlahan',
                'deskripsi' => 'Belajar penjumlahan bilangan 1-10',
                'tipe' => 'materi',
                'kelas' => '1',
                'is_published' => true,
            ],
            [
                'judul' => 'Kuis Bahasa Indonesia Minggu 1',
                'deskripsi' => 'Kuis untuk mengukur pemahaman materi Bahasa Indonesia',
                'tipe' => 'kuis',
                'kelas' => '1',
                'is_published' => true,
                'tanggal_deadline' => now()->addDays(7),
            ],
            [
                'judul' => 'IPA - Pengenalan Hewan',
                'deskripsi' => 'Mengenal berbagai jenis hewan dan habitatnya',
                'tipe' => 'materi',
                'kelas' => '2',
                'is_published' => true,
            ],
            [
                'judul' => 'IPS - Kebudayaan Indonesia',
                'deskripsi' => 'Mempelajari keberagaman budaya Indonesia',
                'tipe' => 'materi',
                'kelas' => '3',
                'is_published' => false,
            ],
        ];

        foreach ($materiData as $data) {
            $data['guru_id'] = $guru->id;
            Materi::create($data);
        }
    }
}
