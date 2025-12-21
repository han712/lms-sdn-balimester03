<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'siswa_id',
        'materi_id',
        'status',
        'waktu_akses', // UBAH: dari 'tanggal' jadi 'waktu_akses' sesuai database
        'durasi_akses',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'waktu_akses' => 'datetime', // UBAH: casting ke datetime
    ];

    protected $appends = [
        'status_badge',
        'status_color',
    ];

    // Status constants
    const STATUS_HADIR = 'hadir';
    const STATUS_SAKIT = 'sakit';
    const STATUS_IZIN = 'izin';
    const STATUS_ALPHA = 'tidak_hadir'; // Sesuaikan dengan enum di migrasi (hadir, tidak_hadir, sakit, izin)

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto set waktu_akses if not provided
        static::creating(function ($absensi) {
            if (!$absensi->waktu_akses) {
                $absensi->waktu_akses = now();
            }
        });
    }

    // --- Relationships ---

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id')->where('role', 'siswa');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    // --- Scopes ---

    public function scopeHadir($query)
    {
        return $query->where('status', self::STATUS_HADIR);
    }

    public function scopeSakit($query)
    {
        return $query->where('status', self::STATUS_SAKIT);
    }

    public function scopeIzin($query)
    {
        return $query->where('status', self::STATUS_IZIN);
    }

    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeByMateri($query, $materiId)
    {
        return $query->where('materi_id', $materiId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        // UBAH: Gunakan waktu_akses
        return $query->whereBetween('waktu_akses', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by month
     */
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        // UBAH: Gunakan waktu_akses
        return $query->whereYear('waktu_akses', $year)
            ->whereMonth('waktu_akses', $month);
    }

    /**
     * Scope for today's absensi
     */
    public function scopeToday($query)
    {
        return $query->whereDate('waktu_akses', today());
    }

    /**
     * Scope for this week's absensi
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('waktu_akses', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's absensi
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('waktu_akses', now()->year)
            ->whereMonth('waktu_akses', now()->month);
    }

    // --- Accessors ---

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_HADIR => '<span class="badge bg-success">Hadir</span>',
            self::STATUS_SAKIT => '<span class="badge bg-warning">Sakit</span>',
            self::STATUS_IZIN => '<span class="badge bg-info">Izin</span>',
            'tidak_hadir' => '<span class="badge bg-danger">Alpha</span>', // Sesuaikan enum
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_HADIR => 'success',
            self::STATUS_SAKIT => 'warning',
            self::STATUS_IZIN => 'info',
            'tidak_hadir' => 'danger',
            default => 'secondary',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->waktu_akses ? $this->waktu_akses->format('d M Y, H:i') : '-';
    }

    public function getShortDateAttribute(): string
    {
        return $this->waktu_akses ? $this->waktu_akses->format('d/m/Y') : '-';
    }

    // --- Helper Methods ---

    public static function createOrUpdate($siswaId, $materiId, $status = self::STATUS_HADIR, $keterangan = null)
    {
        $existing = self::where('siswa_id', $siswaId)
            ->where('materi_id', $materiId)
            ->whereDate('waktu_akses', today())
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                // 'keterangan' => $keterangan, // Hapus jika kolom keterangan tidak ada di DB
            ]);
            return $existing;
        }

        return self::create([
            'siswa_id' => $siswaId,
            'materi_id' => $materiId,
            'status' => $status,
            'waktu_akses' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}