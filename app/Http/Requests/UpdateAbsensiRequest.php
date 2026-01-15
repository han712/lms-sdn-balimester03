<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $materi = $this->route('materi');
        return auth()->user()->isGuru() && 
               $materi->guru_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,tidak_hadir',
            'keterangan' => 'nullable|string|max:500'
        ];
    }
    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa wajib dipilih',
            'siswa_id.exists' => 'Siswa tidak ditemukan',
            'status.required' => 'Status absensi wajib dipilih',
            'status.in' => 'Status harus: hadir, izin, sakit, atau tidak_hadir'
        ];
    }
}
