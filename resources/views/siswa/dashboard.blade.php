@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white rounded-2xl overflow-hidden position-relative">
                <div class="card-body p-4 p-lg-5 position-relative z-index-1">
                    <h1 class="display-6 fw-bold">Halo, {{ auth()->user()->name }}! 👋</h1>
                    <p class="lead mb-0">Kamu berada di Kelas {{ auth()->user()->kelas }}. Ayo selesaikan misimu hari ini!</p>
                </div>
                <i class="bi bi-rocket-takeoff-fill position-absolute text-white-50" style="font-size: 10rem; right: -20px; bottom: -40px; transform: rotate(-15deg);"></i>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-xl h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                            <i class="bi bi-book-half fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0 text-uppercase small fw-bold">Materi</h6>
                    </div>
                    <h2 class="fw-bold mb-0">{{ $stats['total_materi'] }}</h2>
                    <small class="text-success fw-bold"><i class="bi bi-check-lg"></i> {{ $stats['materi_diakses'] }} Diakses</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-xl h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                            <i class="bi bi-trophy-fill fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0 text-uppercase small fw-bold">Tugas Selesai</h6>
                    </div>
                    <h2 class="fw-bold mb-0">{{ $stats['kuis_dijawab'] }}</h2>
                    <small class="text-muted">Hebat!</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-xl h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-warning bg-opacity-10 text-warning p-3 rounded-circle me-3">
                            <i class="bi bi-star-fill fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0 text-uppercase small fw-bold">Rata-rata</h6>
                    </div>
                    <h2 class="fw-bold mb-0">{{ number_format($stats['rata_nilai'], 1) }}</h2>
                    <small class="text-muted">Pertahankan!</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-xl h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger p-3 rounded-circle me-3">
                            <i class="bi bi-puzzle-fill fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0 text-uppercase small fw-bold">Total Kuis</h6>
                    </div>
                    <h2 class="fw-bold mb-0">{{ $stats['total_materi'] }}</h2> <small class="text-muted">Tersedia</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            
            @if($kuis_pending->count() > 0)
            <div class="card border-warning border-2 bg-warning bg-opacity-10 rounded-xl mb-4">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> 
                            Kamu punya {{ $kuis_pending->count() }} Misi Belum Selesai!
                        </h5>
                        <p class="mb-0 text-muted small">Ayo kerjakan sebelum batas waktunya habis.</p>
                    </div>
                </div>
                <div class="list-group list-group-flush mt-3">
                    @foreach($kuis_pending as $kuis)
                    <a href="{{ route('siswa.materi.show', $kuis) }}" class="list-group-item list-group-item-action bg-transparent border-warning border-opacity-25 d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark">{{ $kuis->judul }}</strong>
                            <br>
                            <small class="text-danger">
                                <i class="bi bi-clock"></i> Deadline: {{ $kuis->tanggal_selesai ? \Carbon\Carbon::parse($kuis->tanggal_selesai)->format('d M Y, H:i') : 'Tidak ada' }}
                            </small>
                        </div>
                        <span class="btn btn-sm btn-warning rounded-pill fw-bold">Kerjakan <i class="bi bi-arrow-right"></i></span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-gray-800">📚 Materi Terbaru</h4>
                <a href="{{ route('siswa.materi.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
            </div>

            <div class="row g-3">
                @forelse($available_materi as $materi)
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-xl hover-lift overflow-hidden">
                        <div class="card-body position-relative">
                            <span class="badge position-absolute top-0 end-0 m-3 {{ $materi->tipe == 'kuis' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                {{ strtoupper($materi->tipe) }}
                            </span>

                            <div class="mb-3">
                                <i class="bi {{ $materi->tipe == 'kuis' ? 'bi-pencil-square text-warning' : 'bi-book-half text-primary' }}" style="font-size: 2rem;"></i>
                            </div>

                            <h5 class="card-title fw-bold text-truncate">{{ $materi->judul }}</h5>
                            <p class="card-text text-muted small">
                                Oleh: {{ $materi->guru->name }}
                            </p>
                            
                            <hr class="border-light">
                            
                            <a href="{{ route('siswa.materi.show', $materi) }}" class="btn w-100 rounded-pill {{ $materi->tipe == 'kuis' ? 'btn-warning' : 'btn-primary' }}">
                                Buka {{ ucfirst($materi->tipe) }}
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-emoji-smile fs-1"></i>
                    <p>Belum ada materi baru. Istirahat dulu ya!</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-xl">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">⏱️ Aktivitas Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recent_nilai as $nilai)
                        <div class="list-group-item px-4 py-3 border-light">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold text-primary">Nilai Keluar!</h6>
                                <small class="text-muted">{{ $nilai->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small">Kuis: {{ $nilai->materi->judul }}</p>
                            <span class="badge {{ $nilai->nilai >= 70 ? 'bg-success' : 'bg-danger' }}">
                                Skor: {{ $nilai->nilai }}
                            </span>
                        </div>
                        @endforeach

                        @foreach($recent_absensi as $absen)
                        <div class="list-group-item px-4 py-3 border-light">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold text-dark">Belajar</h6>
                                <small class="text-muted">{{ $absen->waktu_akses ? \Carbon\Carbon::parse($absen->waktu_akses)->diffForHumans() : '' }}</small>
                            </div>
                            <p class="mb-1 small text-muted">Membuka: {{ $absen->materi->judul }}</p>
                        </div>
                        @endforeach
                        
                        @if($recent_nilai->isEmpty() && $recent_absensi->isEmpty())
                        <div class="text-center py-4 text-muted small">Belum ada aktivitas.</div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="{{ route('siswa.riwayat-absensi') }}" class="text-decoration-none fw-bold small">Lihat Semua Riwayat</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection