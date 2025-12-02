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
        'tanggal',
        'keterangan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected $appends = [
        'status_badge',
        'status_color',
    ];

    // Status constants
    const STATUS_HADIR = 'hadir';
    const STATUS_SAKIT = 'sakit';
    const STATUS_IZIN = 'izin';
    const STATUS_ALPHA = 'alpha';

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto set tanggal if not provided
        static::creating(function ($absensi) {
            if (!$absensi->tanggal) {
                $absensi->tanggal = now();
            }
        });
    }

    // --- Relationships ---

    /**
     * Absensi belongs to siswa (User)
     */
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id')->where('role', 'siswa');
    }

    /**
     * Absensi belongs to materi
     */
    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    // --- Scopes ---

    /**
     * Scope for hadir status
     */
    public function scopeHadir($query)
    {
        return $query->where('status', self::STATUS_HADIR);
    }

    /**
     * Scope for sakit status
     */
    public function scopeSakit($query)
    {
        return $query->where('status', self::STATUS_SAKIT);
    }

    /**
     * Scope for izin status
     */
    public function scopeIzin($query)
    {
        return $query->where('status', self::STATUS_IZIN);
    }

    /**
     * Scope for alpha status
     */
    public function scopeAlpha($query)
    {
        return $query->where('status', self::STATUS_ALPHA);
    }

    /**
     * Scope to filter by specific siswa
     */
    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Scope to filter by specific materi
     */
    public function scopeByMateri($query, $materiId)
    {
        return $query->where('materi_id', $materiId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by month
     */
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
    }

    /**
     * Scope for today's absensi
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Scope for this week's absensi
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('tanggal', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's absensi
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month);
    }

    // --- Accessors ---

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_HADIR => '<span class="badge bg-success">Hadir</span>',
            self::STATUS_SAKIT => '<span class="badge bg-warning">Sakit</span>',
            self::STATUS_IZIN => '<span class="badge bg-info">Izin</span>',
            self::STATUS_ALPHA => '<span class="badge bg-danger">Alpha</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_HADIR => 'success',
            self::STATUS_SAKIT => 'warning',
            self::STATUS_IZIN => 'info',
            self::STATUS_ALPHA => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_HADIR => 'check-circle',
            self::STATUS_SAKIT => 'thermometer',
            self::STATUS_IZIN => 'file-text',
            self::STATUS_ALPHA => 'x-circle',
            default => 'help-circle',
        };
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->tanggal->format('d M Y, H:i');
    }

    /**
     * Get formatted date (short)
     */
    public function getShortDateAttribute(): string
    {
        return $this->tanggal->format('d/m/Y');
    }

    // --- Helper Methods ---

    /**
     * Get status options for forms
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_ALPHA => 'Alpha',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return self::getStatusOptions()[$this->status] ?? 'Unknown';
    }

    /**
     * Check if siswa is present (hadir)
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_HADIR;
    }

    /**
     * Check if siswa is absent (sakit, izin, alpha)
     */
    public function isAbsent(): bool
    {
        return in_array($this->status, [
            self::STATUS_SAKIT,
            self::STATUS_IZIN,
            self::STATUS_ALPHA
        ]);
    }

    /**
     * Calculate attendance statistics for a siswa
     */
    public static function getSiswaStatistics($siswaId, $startDate = null, $endDate = null)
    {
        $query = self::where('siswa_id', $siswaId);

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $absensiData = $query->get();
        
        $total = $absensiData->count();
        $hadir = $absensiData->where('status', self::STATUS_HADIR)->count();
        $sakit = $absensiData->where('status', self::STATUS_SAKIT)->count();
        $izin = $absensiData->where('status', self::STATUS_IZIN)->count();
        $alpha = $absensiData->where('status', self::STATUS_ALPHA)->count();
        
        return [
            'total' => $total,
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpha' => $alpha,
            'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
            'persentase_sakit' => $total > 0 ? round(($sakit / $total) * 100, 2) : 0,
            'persentase_izin' => $total > 0 ? round(($izin / $total) * 100, 2) : 0,
            'persentase_alpha' => $total > 0 ? round(($alpha / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate attendance statistics for a materi
     */
    public static function getMateriStatistics($materiId)
    {
        $absensiData = self::where('materi_id', $materiId)->get();
        
        $total = $absensiData->count();
        $hadir = $absensiData->where('status', self::STATUS_HADIR)->count();
        
        return [
            'total' => $total,
            'hadir' => $hadir,
            'sakit' => $absensiData->where('status', self::STATUS_SAKIT)->count(),
            'izin' => $absensiData->where('status', self::STATUS_IZIN)->count(),
            'alpha' => $absensiData->where('status', self::STATUS_ALPHA)->count(),
            'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get attendance summary by date
     */
    public static function getSummaryByDate($startDate, $endDate)
    {
        return self::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir'),
                DB::raw('SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) as sakit'),
                DB::raw('SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin'),
                DB::raw('SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha')
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get attendance summary by mapel
     */
    public static function getSummaryByMapel($siswaId = null)
    {
        $query = self::join('materi', 'absensi.materi_id', '=', 'materi.id')
            ->select(
                'materi.mapel',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN absensi.status = "hadir" THEN 1 ELSE 0 END) as hadir')
            )
            ->groupBy('materi.mapel');

        if ($siswaId) {
            $query->where('absensi.siswa_id', $siswaId);
        }

        return $query->get()->map(function($item) {
            $item->persentase = $item->total > 0 
                ? round(($item->hadir / $item->total) * 100, 2) 
                : 0;
            return $item;
        });
    }

    /**
     * Check if absensi already exists
     */
    public static function exists($siswaId, $materiId, $date = null)
    {
        $date = $date ?? today();
        
        return self::where('siswa_id', $siswaId)
            ->where('materi_id', $materiId)
            ->whereDate('tanggal', $date)
            ->exists();
    }

    /**
     * Create or update absensi
     */
    public static function createOrUpdate($siswaId, $materiId, $status = self::STATUS_HADIR, $keterangan = null)
    {
        $existing = self::where('siswa_id', $siswaId)
            ->where('materi_id', $materiId)
            ->whereDate('tanggal', today())
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'keterangan' => $keterangan,
            ]);
            return $existing;
        }

        return self::create([
            'siswa_id' => $siswaId,
            'materi_id' => $materiId,
            'status' => $status,
            'tanggal' => now(),
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}