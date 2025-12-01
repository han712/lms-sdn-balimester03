<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GuruSeeder::class,
            SiswaSeeder::class,
            MateriSeeder::class,
        ]);
        // 1. Buat Akun GURU Otomatis
        User::create([
            'name' => 'Budi',
            'email' => 'guru@sekolah.com', // Email untuk login
            'password' => Hash::make('password'), // Password: password
            'role' => 'guru', // KUNCI UTAMA: Set role jadi guru
            'nip' => '198001012023011001', // Opsional, sesuaikan struktur tabelmu
            'is_active' => true,
        ]);

        // 2. Buat Akun ADMIN Otomatis (biar punya akses penuh)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // 3. Buat Akun SISWA Otomatis (untuk tes)
        User::create([
            'name' => 'Andi Siswa',
            'email' => 'siswa@sekolah.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'kelas' => '1',
            'is_active' => true,
        ]);
    }
}
