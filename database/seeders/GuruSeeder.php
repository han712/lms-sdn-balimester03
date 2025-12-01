<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gurus = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.guru@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nip' => '198501012010011001',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.guru@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nip' => '198603152011012002',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmad Dahlan',
                'email' => 'ahmad.guru@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nip' => '198709202012011003',
                'is_active' => true,
            ],
        ];

        foreach ($gurus as $guru) {
            User::create($guru);
        }
    }
}
