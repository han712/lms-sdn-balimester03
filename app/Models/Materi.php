<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;
    
    // [PENTING] Mengunci nama tabel
    protected $table = 'materi';

    protected $fillable = [
        'guru_id',
        'judul',
        'deskripsi',
        'file_path',
        'tipe', // materi / kuis
        'kelas',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // --- Relationships ---

    public function guru() 
    { 
        return $this->belongsTo(User::class, 'guru_id'); 
    }

    // Satu materi punya banyak data absensi siswa
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'materi_id');
    }

    // Satu materi (jika kuis) punya banyak jawaban
    public function jawabanKuis()
    {
        return $this->hasMany(JawabanKuis::class, 'materi_id');
    }
}