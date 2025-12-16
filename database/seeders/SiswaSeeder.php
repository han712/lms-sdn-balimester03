<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Materi;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Andi Pratama', 'Budi Hartono', 'Citra Dewi', 'Dina Amelia',
            'Eko Saputra', 'Fitri Handayani', 'Gita Permata', 'Hadi Wijaya',
            'Ika Rahayu', 'Joko Susilo', 'Kartika Sari', 'Lina Marlina',
            'Made Suryadi', 'Nina Kusuma', 'Omar Bakri', 'Putri Andini'
        ];

        $nisn = 1001;
        
        foreach (range(1, 6) as $kelas) {
            foreach ($names as $name) {
                User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)) . ".kelas{$kelas}@siswa.com",
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'nisn' => (string) $nisn++,
                    'kelas' => $kelas,
                    'is_active' => true,
                ]);
            }
        }
    }
}