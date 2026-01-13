<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Tambahan: Biar lebar kolom otomatis
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $materiId;

    public function __construct($materiId)
    {
        $this->materiId = $materiId;
    }

    public function collection()
    {
        return Absensi::with(['siswa', 'materi'])
            ->where('materi_id', $this->materiId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Materi',
            'Status',
            'Waktu Akses',
        ];
    }

    public function map($absensi): array
    {
        static $no = 0;
        $no++;

        // Ubah Status jadi huruf besar (hadir -> Hadir)
        $status = ucfirst($absensi->status); 

        // Format tanggal pakai Carbon bawaan Laravel (Lebih Aman)
        $tanggal = $absensi->waktu_akses 
            ? Carbon::parse($absensi->waktu_akses)->translatedFormat('d F Y H:i') 
            : '-';
        
        return [
            $no,
            $absensi->siswa->nisn ?? '-', // Pakai ?? untuk jaga2 kalau kosong
            $absensi->siswa->name ?? 'Siswa Terhapus',
            $absensi->siswa->kelas ?? '-',
            $absensi->materi->judul,
            $status,
            $tanggal,
        ];
    }

    // Bonus: Bikin Header jadi Bold
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}