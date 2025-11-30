<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isGuru();
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:5000',
            'tipe' => 'required|in:materi,kuis',
            'kelas' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4,avi|max:51200', // Max 50MB
            'is_published' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul materi wajib diisi',
            'judul.max' => 'Judul maksimal 255 karakter',
            'deskripsi.required' => 'Deskripsi materi wajib diisi',
            'tipe.required' => 'Tipe materi wajib dipilih',
            'tipe.in' => 'Tipe harus materi atau kuis',
            'kelas.required' => 'Kelas wajib dipilih',
            'kelas.in' => 'Kelas harus antara 1-6',
            'file.mimes' => 'File harus berformat: pdf, doc, docx, ppt, pptx, jpg, jpeg, png, mp4, avi',
            'file.max' => 'Ukuran file maksimal 50MB'
        ];
    }
}