<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        
        // Identity Keys
        'nisn', 'nis', 'nip', 'id_pegawai',
        
        // Siswa
        'kelas', 'tahun_masuk', 'tempat_lahir', 'tanggal_lahir', 
        'jenis_kelamin', 'agama', 'nama_ayah', 'nama_ibu', 
        'no_hp_ortu', 'pekerjaan_ortu',

        // Guru
        'status_kepegawaian', 'jabatan_tambahan', 'wali_kelas', 
        'mata_pelajaran_utama', 'pendidikan_terakhir',

        // Admin
        'posisi', 'level_akses',
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
            'tanggal_lahir' => 'date', // Biar otomatis jadi object Carbon
            'is_active' => 'boolean',// Casting boolean agar aman
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
    
    // [PENTING] Relasi Guru ke Materi buatannya
    public function materi()
    {
        return $this->hasMany(Materi::class, 'guru_id');
    }

    public function absensi(){
        return $this->hasMany(Absensi::class, 'siswa_id');
    }
    
    public function jawabanKuis(){
        return $this->hasMany(JawabanKuis::class, 'siswa_id');
    }
    
    public function jawabanYangDinilai(){
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
    
    // Scope baru untuk mengambil siswa super admin (jika perlu)
    public function scopeSuperAdmin($query)
    {
        // Asumsi super admin adalah admin pertama atau admin yang aktif
        return $query->where('role', 'admin');
    }

    public function scopeKelas($query, $kelas)
    {
        return $query->where('kelas', $kelas);
    }

    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Admin',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => 'Unknown'
        };
    }
}