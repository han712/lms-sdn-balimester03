<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Menangani filter dan pencarian user untuk halaman index
     */
    public function getFilteredUsers($request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        return $query->orderBy($sortBy, $sortDirection)
                     ->paginate(20)
                     ->withQueryString();
    }

    /**
     * Logic membuat user baru dengan pembersihan data
     */
    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            
            // Konversi checkbox 'on'/'1' menjadi boolean true/false
            $data['is_active'] = isset($data['is_active']) && ($data['is_active'] == '1' || $data['is_active'] === true);

            // Bersihkan data yang tidak relevan dengan role yang dipilih
            if ($data['role'] !== 'siswa') {
                $data['nisn'] = null;
                $data['kelas'] = null;
                // Hapus field siswa dari array
                unset($data['nis'], $data['tahun_masuk'], $data['nama_ibu'], $data['tanggal_lahir'], $data['jenis_kelamin']); 
            }
            
            if ($data['role'] !== 'guru') {
                $data['nip'] = null;
                // Hapus field guru dari array
                unset($data['status_kepegawaian'], $data['mata_pelajaran_utama'], $data['pendidikan_terakhir']);
            }
            
            if ($data['role'] !== 'admin') {
                // Hapus field admin dari array jika ada
                unset($data['id_pegawai'], $data['posisi']);
            }

            return User::create($data);
        });
    }

    /**
     * Logic update user
     */
    public function updateUser(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            // Handle Password: jika kosong, jangan di-update
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $data['is_active'] = isset($data['is_active']) && ($data['is_active'] == '1' || $data['is_active'] === true);

            // Bersihkan data saat update
            if ($data['role'] !== 'siswa') {
                $data['nisn'] = null;
                $data['kelas'] = null;
            }
            if ($data['role'] !== 'guru') {
                $data['nip'] = null;
            }

            return $user->update($data);
        });
    }

    /**
     * Logic hapus user dengan validasi safety
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            throw new \Exception('Tidak dapat menghapus akun sendiri');
        }

        // Cek jika user adalah admin dan jumlah admin tinggal 1
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            throw new \Exception('Tidak dapat menghapus super admin terakhir');
        }

        return $user->delete();
    }
}