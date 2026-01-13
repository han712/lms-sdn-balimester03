<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Soal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Casting JSON opsi jawaban agar otomatis jadi Array saat dipanggil
    protected $casts = [
        'opsi_jawaban' => 'array',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }
}
