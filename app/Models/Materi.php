<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'guru_id',
        'judul',
        'keterangan',
        'tipe',
        'kelas',
        'file',
        'link',
        'video',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_published',
    ];

    protected $casts = [
        'is_published'    => 'boolean',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relationships
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'materi_id');
    }

    public function jawabanKuis()
    {
        return $this->hasMany(JawabanKuis::class, 'materi_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'materi_id');
    }

    // Helpers
    public function getFileUrlAttribute()
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }
}