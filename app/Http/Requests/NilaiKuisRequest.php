<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NilaiKuisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $jawaban = $this->route('jawaban');
        return auth()->user()->isGuru() && 
               $jawaban->materi->guru_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nilai' => 'required|integer|min:0|max:100',
            'catatan_guru' => 'nullable|string|max:1000'
        ];
    }
    public function messages(): array
    {
        return [
            'nilai.required' => 'Nilai wajib diisi',
            'nilai.integer' => 'Nilai harus berupa angka',
            'nilai.min' => 'Nilai minimal 0',
            'nilai.max' => 'Nilai maksimal 100',
            'catatan_guru.max' => 'Catatan maksimal 1000 karakter'
        ];
    }
}
