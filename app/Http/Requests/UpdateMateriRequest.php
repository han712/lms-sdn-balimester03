<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isGuru() && 
               $this->route('materi')->guru_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:5000',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200',
            'is_published' => 'boolean',
            'remove_file' => 'boolean'
        ];
    }
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul materi wajib diisi',
            'deskripsi.required' => 'Deskripsi materi wajib diisi',
            'tipe.in' => 'Tipe harus materi atau kuis',
            'kelas.in' => 'Kelas harus antara 1-6',
            'file.max' => 'Ukuran file maksimal 50MB'
        ];
    }
}
