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
        'keterangan', // Ganti 'deskripsi' jadi 'keterangan'
        'file',       // Ganti 'file_path' jadi 'file'
        'tipe',
        'kelas',
        'tanggal_mulai',   // Tambahkan ini (belum ada di model sebelumnya)
        'tanggal_selesai', // Tambahkan ini
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'tanggal_deadline' => 'datetime',
        'views_count' => 'integer',
        'file_size' => 'integer',
    ];

    // --- Relationships ---
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeByKelas($query, $kelas)
    {
        return $query->where('kelas', $kelas);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getFileSizeFormatAttribute()
    {
        if (!$this->file_size) return null;
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->tanggal_deadline) return false;
        return now()->gt($this->tanggal_deadline);
    }

    // Add method to increment views
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public static function getKelasOptions()
    {
        return [1, 2, 3, 4, 5, 6];
    }

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