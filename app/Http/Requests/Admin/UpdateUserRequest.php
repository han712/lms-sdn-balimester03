<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

   public function rules()
    {
        // Ambil ID user yang sedang diedit agar validasi unique tidak error pada diri sendiri
        $userId = $this->route('user')->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            // Password bersifat opsional saat edit (hanya diisi jika ingin diganti)
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'is_active' => ['nullable'],
        ];

        // LOGIKA KONTROL SISWA
        if ($this->role == 'siswa') {
            $rules += [
                // Validasi data khusus siswa
                'nisn' => ['required', 'max:20', Rule::unique('users')->ignore($userId)],
                'nis' => ['nullable', 'max:20', Rule::unique('users')->ignore($userId)],
                'kelas' => ['required', Rule::in(config('lms.daftar_kelas'))],
                'tahun_masuk' => 'required|digits:4',
                'jenis_kelamin' => 'required|in:L,P',
                'nama_ibu' => 'required|string',
                'tanggal_lahir' => 'required|date',
                // Data opsional
                'tempat_lahir' => 'nullable|string',
                'nama_ayah' => 'nullable|string',
                'no_hp_ortu' => 'nullable|string',
                'pekerjaan_ortu' => 'nullable|string',
            ];
        } 
        
        // LOGIKA KONTROL GURU
        elseif ($this->role == 'guru') {
            $rules += [
                // Validasi data khusus guru
                'nip' => ['required', 'max:30', Rule::unique('users')->ignore($userId)],
                'status_kepegawaian' => 'required|in:PNS,GTY,GTT',
                'pendidikan_terakhir' => 'required|string',
                'mata_pelajaran_utama' => 'required|string',
                // Data opsional
                'wali_kelas' => 'nullable|integer|between:1,6',
                'jabatan_tambahan' => 'nullable|string',
            ];
        } 
        
        // LOGIKA KONTROL ADMIN
        elseif ($this->role == 'admin') {
            $rules += [
                'id_pegawai' => ['nullable', Rule::unique('users')->ignore($userId)],
                'posisi' => 'nullable|string',
            ];
        }

        return $rules;
    }
}
