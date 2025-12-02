<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nisn',
        'nip',
        'kelas',
        'is_active',
        'last_activity',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'mapel',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // --- Helper Methods ---
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool 
    {
        return $this->role === 'siswa';
    }

    // --- Relationships ---
    public function materi()
    {
        return $this->hasMany(Materi::class, 'guru_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    public function jawabanKuis()
    {
        return $this->hasMany(JawabanKuis::class, 'siswa_id');
    }

    public function jawabanYangDinilai()
    {
        return $this->hasMany(JawabanKuis::class, 'dinilai_oleh');
    }

    // --- Scopes ---
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }

    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }

    public function scopeSuperAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeKelas($query, $kelas)
    {
        return $query->where('kelas', $kelas);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => 'Unknown',
        };
    }

    /**
     * ✅ Akses foto otomatis dari public/FotoGuru/
     * jika file ada → tampilkan
     * jika tidak → fallback placeholder
     */
    public function getFotoUrlAttribute(): string
    {   
    $basePath = public_path('FotoGuru/');
    $extensions = ['jpg', 'jpeg', 'png'];

    foreach ($extensions as $ext) {
        $filename = $this->name . '.' . $ext;
        if (file_exists($basePath . $filename)) {
            return asset('FotoGuru/' . $filename);
        }
    }

    return 'https://via.placeholder.com/400x400?text=Foto+Guru';
    }
}
