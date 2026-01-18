<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Kontrol Logika Pembuatan User Baru
     */
    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Enkripsi Password
            $data['password'] = Hash::make($data['password']);
            
            // 2. Set default status aktif jika tidak dipilih
            $data['is_active'] = $data['is_active'] ?? true;

            // 3. Simpan hanya data yang relevan sesuai Role (Guru/Siswa)
            return User::create($this->filterDataByRole($data));
        });
    }

    /**
     * Kontrol Logika Update User
     */
    public function updateUser(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Cek apakah password diisi? Jika kosong, hapus dari array agar password lama tidak hilang
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }

            // 2. Update data (menggunakan filter agar data guru tidak masuk ke siswa, dst)
            $user->update($this->filterDataByRole($data));
            
            return $user;
        });
    }

    /**
     * Kontrol Logika Hapus User
     */
    public function deleteUser(User $user)
    {
        // Proteksi: Admin tidak boleh menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            throw new \Exception("Anda tidak dapat menghapus akun Anda sendiri.");
        }
        
        // Proteksi: Jangan hapus Admin terakhir
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
             throw new \Exception("Tidak dapat menghapus admin terakhir.");
        }

        return $user->delete();
    }

    /**
     * Logic Filter Pencarian User (Untuk Halaman Index)
     */
    public function getFilteredUsers($request)
    {
        $query = User::query();

        // Filter Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter Kelas (Khusus melihat data Siswa per kelas)
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // Search Global (Nama, Email, NIP, NISN)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    /**
     * Helper Penting: Membersihkan field yang tidak perlu.
     * Contoh: Jika role 'Siswa', maka data 'NIP' atau 'Gaji' tidak akan disimpan meski terkirim.
     */
    private function filterDataByRole(array $data)
    {
        $role = $data['role'];
        
        // Field Dasar (Semua user punya)
        $commonFields = ['name', 'email', 'password', 'role', 'is_active', 'tempat_lahir', 'tanggal_lahir'];

        // Field Spesifik Role
        $roleFields = [
            'admin' => ['id_pegawai', 'posisi'],
            
            'guru'  => [
                'nip', 'status_kepegawaian', 'pendidikan_terakhir', 
                'mata_pelajaran_utama', 'wali_kelas', 'jabatan_tambahan'
            ],
            
            'siswa' => [
                'nisn', 'nis', 'kelas', 'tahun_masuk', 'jenis_kelamin', 
                'nama_ibu', 'nama_ayah', 'no_hp_ortu', 'pekerjaan_ortu'
            ]
        ];

        // Gabungkan field umum + field khusus role yang dipilih
        $allowedFields = array_merge($commonFields, $roleFields[$role] ?? []);

        // Hanya ambil data yang kuncinya ada di $allowedFields
        return array_intersect_key($data, array_flip($allowedFields));
    }
}