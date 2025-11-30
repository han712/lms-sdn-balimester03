<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanKuis extends Model
{
    use HasFactory;

    // [PENTING] Mengunci nama tabel agar tidak dicari sebagai 'jawaban_kuis_s'
    protected $table = 'jawaban_kuis';

    protected $fillable = [
        'siswa_id',
        'materi_id',
        'jawaban_file', // atau 'isi_jawaban' sesuaikan database kamu
        'nilai',
        'komentar_guru',
        'dinilai_oleh', // ID guru yang menilai
        'tanggal_dinilai'
    ];

    protected $casts = [
        'nilai' => 'integer',
        'tanggal_dinilai' => 'datetime',
    ];

    // --- Scopes ---
    
    // Scope yang dipanggil di AdminController: ->sudahDinilai()
    public function scopeSudahDinilai($query)
    {
        return $query->whereNotNull('nilai');
    }

    // --- Relationships ---
    public function siswa() { return $this->belongsTo(User::class, 'siswa_id'); }
    public function materi() { return $this->belongsTo(Materi::class, 'materi_id'); }
    
    // Relasi ke Guru yang menilai
    public function penilai() { return $this->belongsTo(User::class, 'dinilai_oleh'); }
}