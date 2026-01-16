<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'is_active' => ['nullable'],
        ];

        if ($this->role == 'siswa') {
            $rules += [
                'nisn' => 'required|unique:users,nisn|max:20',
                'nis' => 'nullable|unique:users,nis|max:20',
                'kelas' => ['required', Rule::in(config('lms.daftar_kelas'))],
                'tahun_masuk' => 'required|digits:4',
                'jenis_kelamin' => 'required|in:L,P',
                'nama_ibu' => 'required|string',
                'tanggal_lahir' => 'required|date',
            ];
        } elseif ($this->role == 'guru') {
            $rules += [
                'nip' => 'required|unique:users,nip|max:30',
                'status_kepegawaian' => 'required|in:PNS,GTY,GTT',
                'pendidikan_terakhir' => 'required|string',
                'mata_pelajaran_utama' => 'required|string',
            ];
        } elseif ($this->role == 'admin') {
            $rules += [
                'id_pegawai' => 'nullable|unique:users,id_pegawai',
                'posisi' => 'nullable|string',
            ];
        }

        return $rules;
    }
}