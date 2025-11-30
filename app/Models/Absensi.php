<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    // [PENTING] Laravel akan mencari tabel 'absensis' jika ini tidak ada
    protected $table = 'absensi';

    // [PENTING] Wajib ada agar bisa create/update
    protected $fillable = [
        'siswa_id',
        'materi_id',
        'status', // hadir, izin, sakit, alpa
        'waktu_akses',
    ];

    protected $casts = [
        'waktu_akses' => 'datetime',
    ];

    // --- Scopes ---
    public function scopeByKelas($query, $kelas)
    {
        return $query->whereHas('siswa', function($q) use ($kelas) {
            $q->where('kelas', $kelas);
        });
    }

    // --- Relationships ---
    public function siswa() { return $this->belongsTo(User::class, 'siswa_id'); }
    public function materi() { return $this->belongsTo(Materi::class, 'materi_id'); }
}