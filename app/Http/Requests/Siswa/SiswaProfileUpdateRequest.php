<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class SiswaProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pastikan hanya user dengan role 'siswa' yang boleh akses
        return $this->user()->isSiswa();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            
            'email' => [
                'required', 
                'email', 
                'max:255', 
                Rule::unique(User::class)->ignore($userId), // Boleh pakai email sendiri
            ],

            'nisn' => [
                'required',
                'numeric',
                // Pastikan NISN unik, kecuali milik user yang sedang login
                Rule::unique(User::class)->ignore($userId),
            ],

            'kelas' => ['required', 'in:1,2,3,4,5,6'], // Sesuaikan opsi kelas SD

            'avatar' => [
                'nullable',
                'image',             // Harus file gambar
                'mimes:jpg,jpeg,png,webp', // Format yang diizinkan
                'max:2048',          // Maksimal 2MB
            ],
        ];
    }

    /**
     * Custom pesan error bahasa Indonesia
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan siswa lain.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.numeric' => 'NISN harus berupa angka.',
            'nisn.unique' => 'NISN ini sudah terdaftar.',
            'kelas.required' => 'Silakan pilih kelas.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }
}