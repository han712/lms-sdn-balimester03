@extends('layouts.app')

@section('title', 'Riwayat Nilai Kuis')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-6">
            <h1 class="h3 fw-bold text-gray-800">🏆 Nilai & Hasil Kuis</h1>
            <p class="text-muted mb-0">Lihat hasil jerih payah belajarmu di sini.</p>
        </div>
        <div class="col-md-6">
            <div class="row g-2">
                <div class="col-4">
                    <div class="bg-white p-3 rounded-xl shadow-sm text-center border-bottom border-primary border-4">
                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Rata-rata</small>
                        <span class="h4 fw-bold text-dark">{{ number_format($stats['rata_rata'], 1) }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-white p-3 rounded-xl shadow-sm text-center border-bottom border-success border-4">
                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Tertinggi</small>
                        <span class="h4 fw-bold text-success">{{ $stats['tertinggi'] }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-white p-3 rounded-xl shadow-sm text-center border-bottom border-warning border-4">
                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Total Kuis</small>
                        <span class="h4 fw-bold text-dark">{{ $stats['total_kuis'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @forelse($riwayat as $item)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-xl hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            {{ $item->materi->mapel ?? 'Umum' }}
                        </span>
                        <small class="text-muted">{{ $item->created_at->format('d M Y') }}</small>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $item->materi->judul }}</h5>
                    <p class="text-muted small mb-3">Guru: {{ $item->materi->guru->name }}</p>

                    <hr class="border-light border-2 border-dashed">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($item->nilai !== null)
                                <div class="d-flex align-items-center">
                                    <h2 class="fw-bold mb-0 me-2 {{ $item->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                        {{ $item->nilai }}
                                    </h2>
                                    <div class="lh-1">
                                        <small class="d-block text-muted" style="font-size: 0.7rem;">SKOR</small>
                                        <small class="fw-bold {{ $item->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                            {{ $item->nilai >= 70 ? 'LULUS' : 'REMEDIAL' }}
                                        </small>
                                    </div>
                                </div>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-hourglass-split"></i> Menunggu Dinilai
                                </span>
                            @endif
                        </div>
                        
                        <a href="{{ route('siswa.materi.show', $item->materi_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    @if($item->komentar)
                    <div class="mt-3 p-2 bg-light rounded border-start border-3 border-info">
                        <small class="text-muted fst-italic">"{{ $item->komentar }}"</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 py-5 text-center text-muted">
            <div class="mb-3">
                <i class="bi bi-clipboard-x fs-1"></i>
            </div>
            <h5>Belum ada kuis yang dikerjakan</h5>
            <a href="{{ route('siswa.materi.index', ['tipe' => 'kuis']) }}" class="btn btn-primary mt-3">Cari Kuis Sekarang</a>
        </div>
        @endforelse
    </div>
    
    <div class="mt-4 d-flex justify-content-center">
        {{ $riwayat->links() }}
    </div>
</div>
@endsection