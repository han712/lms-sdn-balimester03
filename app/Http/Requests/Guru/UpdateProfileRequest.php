<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pastikan hanya guru yang bisa akses
        return $this->user()->isGuru();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Validasi unik NIP, tapi abaikan (ignore) user yang sedang login
            'nip' => [
                'required', 
                'numeric', 
                Rule::unique(User::class)->ignore($this->user()->id)
            ],
            
            'status_kepegawaian' => ['required'], // Tambah ini
            'pendidikan_terakhir' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.numeric' => 'NIP harus berupa angka.',
            'nip.unique' => 'NIP ini sudah digunakan oleh guru lain.',
        ];
    }
}
