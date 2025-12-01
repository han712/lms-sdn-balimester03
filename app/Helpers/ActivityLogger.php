<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;


class ActivityLogger
{
    /**
     * Create a new class instance.
     */
    public static function log(string $activityType, string $description, array $metadata = [])
    {
        if (!auth()->check()) return;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => $activityType,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function logMateriCreated($materi)
    {
        self::log(
            'create_materi',
            "Membuat materi: {$materi->judul}",
            [
                'materi_id' => $materi->id,
                'tipe' => $materi->tipe,
                'kelas' => $materi->kelas,
            ]
        );
    }

    public static function logMateriUpdated($materi)
    {
        self::log(
            'update_materi',
            "Mengupdate materi: {$materi->judul}",
            ['materi_id' => $materi->id]
        );
    }

    public static function logAbsensiUpdated($absensi)
    {
        self::log(
            'update_absensi',
            "Mengupdate absensi siswa: {$absensi->siswa->name}",
            [
                'absensi_id' => $absensi->id,
                'status' => $absensi->status,
            ]
        );
    }

    public static function logKuisDinilai($jawaban)
    {
        self::log(
            'nilai_kuis',
            "Menilai jawaban kuis siswa: {$jawaban->siswa->name}",
            [
                'jawaban_id' => $jawaban->id,
                'nilai' => $jawaban->nilai,
            ]
        );
    }
}
