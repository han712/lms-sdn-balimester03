@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-gray-800">📅 Riwayat Absensi</h1>
            <p class="text-muted">Daftar kehadiran kamu saat membuka materi.</p>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border px-3">
            <small class="text-muted fw-bold d-block">Total Kehadiran</small>
            <span class="h4 fw-bold text-primary mb-0">{{ $riwayat->total() }}</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-2xl overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 border-0">Tanggal & Waktu</th>
                        <th class="py-3 px-4 border-0">Materi Pelajaran</th>
                        <th class="py-3 px-4 border-0">Guru</th>
                        <th class="py-3 px-4 border-0 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $item)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $item->waktu_akses ? $item->waktu_akses->format('d M Y') : '-' }}</span>
                                <small class="text-muted">{{ $item->waktu_akses ? $item->waktu_akses->format('H:i') : '-' }} WIB</small>
                            </div>
                        </td>
                        <td class="px-4">
                            @if($item->materi)
                                <a href="{{ route('siswa.materi.show', $item->materi_id) }}" class="text-decoration-none fw-bold text-primary">
                                    {{ $item->materi->judul }}
                                </a>
                                <div class="small text-muted">{{ ucfirst($item->materi->tipe) }}</div>
                            @else
                                <span class="text-danger fst-italic">Materi telah dihapus</span>
                            @endif
                        </td>
                        <td class="px-4">
                            {{ $item->materi->guru->name ?? '-' }}
                        </td>
                        <td class="px-4 text-center">
                            @if($item->status == 'hadir')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Hadir
                                </span>
                            @elseif($item->status == 'izin')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                    <i class="bi bi-info-circle-fill me-1"></i> Izin
                                </span>
                            @elseif($item->status == 'sakit')
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                    <i class="bi bi-bandaid-fill me-1"></i> Sakit
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i> tidak_hadir
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            Belum ada riwayat absensi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4 d-flex justify-content-center">
        {{ $riwayat->links() }}
    </div>
</div>
@endsection