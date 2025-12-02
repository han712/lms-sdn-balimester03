{{-- resources/views/guru/dashboard.blade.php --}}
@extends('layouts.guru')

@section('title', 'Dashboard Guru')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('guru.materi.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm"></i> Buat Materi Baru
            </a>
            <a href="{{ route('guru.laporan.absensi') }}" class="btn btn-info btn-sm shadow-sm">
                <i class="fas fa-download fa-sm"></i> Export Laporan
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row">
        <!-- Total Materi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Materi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_materi'] }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <span class="text-success">{{ $stats['published_materi'] }} Published</span> |
                                <span class="text-warning">{{ $stats['draft_materi'] }} Draft</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Kuis -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Kuis
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_kuis'] }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <span class="text-warning">{{ $stats['jawaban_belum_dinilai'] }} Belum Dinilai</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kehadiran -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Kehadiran
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['absensi_hadir'] }}/{{ $stats['total_absensi'] }}
                            </div>
                            <div class="progress progress-sm mt-2">
                                @php
                                    $persentase = $stats['total_absensi'] > 0 ? ($stats['absensi_hadir'] / $stats['total_absensi']) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: {{ $persentase }}%" 
                                     aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Nilai -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rata-rata Nilai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['rata_rata_nilai'], 1) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                Dari {{ $stats['jawaban_sudah_dinilai'] }} jawaban
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Materi per Kelas -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Materi per Kelas</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartMateriPerKelas"></canvas>
                </div>
            </div>
        </div>

        <!-- Materi per Bulan -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">Materi per Bulan</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartMateriPerBulan"></canvas>
                </div>
            </div>
        </div>

        <!-- Absensi Status -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">Status Absensi Bulan Ini</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartAbsensiStatus"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Kuis Pending -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Kuis Perlu Dinilai
                    </h6>
                    <span class="badge badge-warning">{{ $kuisPending->count() }}</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($kuisPending as $jawaban)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $jawaban->siswa->name }}</h6>
                            <p class="mb-1 text-sm text-muted">
                                {{ $jawaban->materi->judul }}
                            </p>
                            <small class="text-muted">
                                <i class="far fa-clock"></i> {{ $jawaban->days_waiting }} hari yang lalu
                                @if($jawaban->priority === 'high')
                                    <span class="badge badge-danger ml-2">Urgent</span>
                                @elseif($jawaban->priority === 'medium')
                                    <span class="badge badge-warning ml-2">Medium</span>
                                @endif
                            </small>
                        </div>
                        <div>
                            <a href="{{ route('guru.kuis.detail', $jawaban->id) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Nilai
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Semua kuis sudah dinilai!</p>
                    </div>
                    @endforelse
                </div>
                @if($kuisPending->count() > 0)
                <div class="card-footer text-center">
                    <a href="{{ route('guru.materi.index', ['tipe' => 'kuis']) }}" 
                       class="small">Lihat Semua Kuis</a>
                </div>
                @endif
            </div>
        </div>

        <!-- Materi Terbaru -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Materi Terbaru
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentMateri as $materi)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="mr-3">
                            @if($materi->tipe === 'kuis')
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                            @else
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $materi->judul }}</h6>
                            <small class="text-muted">
                                Kelas {{ $materi->kelas }} • 
                                {{ $materi->created_at->diffForHumans() }}
                            </small>
                            <div class="mt-1">
                                @if($materi->is_published)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                                @if($materi->tipe === 'kuis')
                                    <span class="badge badge-info">
                                        {{ $materi->jawaban_kuis_count }} Jawaban
                                    </span>
                                @else
                                    <span class="badge badge-info">
                                        {{ $materi->absensi_hadir_count }}/{{ $materi->absensi_count }} Hadir
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('guru.materi.show', $materi->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada materi</p>
                        <a href="{{ route('guru.materi.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Buat Materi
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Siswa Row -->
    <div class="row">
        <!-- Siswa Paling Aktif -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-trophy"></i> Siswa Paling Aktif
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($siswaAktif as $index => $item)
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <div class="ranking-badge ranking-{{ $index + 1 }}">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $item->siswa->name }}</h6>
                            <small class="text-muted">
                                NISN: {{ $item->siswa->nisn }} • Kelas {{ $item->siswa->kelas }}
                            </small>
                        </div>
                        <div>
                            <span class="badge badge-success badge-pill">
                                {{ $item->total_hadir }} Hadir
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data kehadiran bulan ini</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Siswa Perlu Perhatian -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-circle"></i> Siswa Perlu Perhatian
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($siswaPerluPerhatian as $item)
                    <div class="alert alert-danger d-flex align-items-center mb-2" role="alert">
                        <i class="fas fa-user-times mr-3"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $item->siswa->name }}</h6>
                            <small>
                                NISN: {{ $item->siswa->nisn }} • Kelas {{ $item->siswa->kelas }}
                            </small>
                        </div>
                        <div>
                            <span class="badge badge-danger badge-pill">
                                {{ $item->total_alpha }}x Alpha
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-smile fa-3x text-success mb-3"></i>
                        <p class="text-muted">Semua siswa aktif! Tidak ada yang perlu perhatian khusus.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivitas Guru -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Aktivitas Anda Bulan Ini
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h4 class="font-weight-bold">{{ $aktivitasGuru['materi_dibuat_bulan_ini'] }}</h4>
                                <p class="text-muted mb-0">Materi Dibuat</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="font-weight-bold">{{ $aktivitasGuru['kuis_dinilai_bulan_ini'] }}</h4>
                                <p class="text-muted mb-0">Kuis Dinilai</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <i class="fas fa-sign-in-alt fa-2x text-info mb-2"></i>
                                <h4 class="font-weight-bold">{{ $aktivitasGuru['total_login_bulan_ini'] }}</h4>
                                <p class="text-muted mb-0">Total Login</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ranking-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    color: white;
}

.ranking-1 {
    background: linear-gradient(135deg, #FFD700, #FFA500);
}

.ranking-2 {
    background: linear-gradient(135deg, #C0C0C0, #808080);
}

.ranking-3 {
    background: linear-gradient(135deg, #CD7F32, #8B4513);
}

.ranking-4, .ranking-5 {
    background: linear-gradient(135deg, #6c757d, #495057);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Chart Materi per Kelas
const ctxKelas = document.getElementById('chartMateriPerKelas').getContext('2d');
new Chart(ctxKelas, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($materiPerKelas)) !!}.map(k => 'Kelas ' + k),
        datasets: [{
            data: {!! json_encode(array_values($materiPerKelas)) !!},
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', 
                '#f6c23e', '#e74a3b', '#858796'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Chart Materi per Bulan
const ctxBulan = document.getElementById('chartMateriPerBulan').getContext('2d');
new Chart(ctxBulan, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($materiPerBulan, 'label')) !!},
        datasets: [{
            label: 'Materi Dibuat',
            data: {!! json_encode(array_column($materiPerBulan, 'total')) !!},
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Chart Absensi Status
const ctxAbsensi = document.getElementById('chartAbsensiStatus').getContext('2d');
new Chart(ctxAbsensi, {
    type: 'bar',
    data: {
        labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
        datasets: [{
            label: 'Jumlah',
            data: [
                {{ $absensiPerStatus['hadir'] ?? 0 }},
                {{ $absensiPerStatus['izin'] ?? 0 }},
                {{ $absensiPerStatus['sakit'] ?? 0 }},
                {{ $absensiPerStatus['alpha'] ?? 0 }}
            ],
            backgroundColor: [
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
@endsection