<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanKuis extends Model
{
    use HasFactory;

    protected $table = 'jawaban_kuis';

    protected $fillable = [
        'siswa_id',
        'materi_id',

        // skenario upload file
        'jawaban_file',
        'file_path', // kalau ada data lama

        // skenario interaktif (PG+Essay)
        'jawaban',       // <-- PENTING

        // penilaian
        'nilai',
        'catatan_guru',
        'catatan_siswa',
        'dinilai_oleh',
        'dinilai_pada',
    ];

    protected $casts = [
        'jawaban' => 'array',       // <-- PENTING (biar $jawaban->jawaban jadi array)
        'nilai' => 'integer',
        'dinilai_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}