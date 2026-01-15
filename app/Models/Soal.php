<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    // ✅ PENTING: samakan dengan nama tabel di database
    protected $table = 'soals';

    protected $fillable = [
        'materi_id',
        'tipe_soal',
        'pertanyaan',
        'gambar',
        'opsi_jawaban',
        'kunci_jawaban',
        'bobot_nilai',
    ];

    // ✅ biar opsi_jawaban otomatis jadi array
    protected $casts = [
        'opsi_jawaban' => 'array',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }
}