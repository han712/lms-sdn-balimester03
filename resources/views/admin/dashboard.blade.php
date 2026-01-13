@extends('layouts.app') {{-- Pastikan layout ini memuat Bootstrap & SB Admin 2 --}}

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Super Admin</h1>
        {{-- Tanggal hari ini --}}
        <span class="d-none d-sm-inline-block text-gray-600 small">{{ now()->format('d M Y') }}</span>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                            <div class="mt-2 small">
                                <span class="text-success"><i class="fas fa-circle"></i> {{ $stats['active_users'] }} Aktif</span>
                                <span class="text-danger ml-2"><i class="fas fa-circle"></i> {{ $stats['inactive_users'] }} Nonaktif</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Guru</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_guru'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Siswa</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_siswa'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Konten</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_materi'] }}</div>
                            <div class="mt-2 small text-muted">
                                {{ $stats['total_kuis'] }} Kuis
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Siswa per Kelas</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="chartSiswaKelas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Absensi Global</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="chartAbsensi"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Terbaru</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary shadow-sm">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_users as $user)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $user->name }}</span><br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        @if($user->role == 'admin') <span class="badge badge-danger">Admin</span>
                                        @elseif($user->role == 'guru') <span class="badge badge-success">Guru</span>
                                        @else <span class="badge badge-info">Siswa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Materi Terbaru</h6>
                    <a href="{{ route('admin.materi.index') }}" class="btn btn-sm btn-primary shadow-sm">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Guru</th>
                                    <th>Tipe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_materi as $materi)
                                <tr>
                                    <td>{{ $materi->judul }}</td>
                                    <td>{{ $materi->guru->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($materi->tipe == 'kuis') <span class="badge badge-warning">Kuis</span>
                                        @else <span class="badge badge-primary">Materi</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // --- Chart Siswa Per Kelas ---
    var ctxKelas = document.getElementById("chartSiswaKelas");
    var chartKelas = new Chart(ctxKelas, {
        type: 'bar',
        data: {
            labels: ["Kelas 1", "Kelas 2", "Kelas 3", "Kelas 4", "Kelas 5", "Kelas 6"],
            datasets: [{
                label: "Jumlah Siswa",
                backgroundColor: "#4e73df",
                hoverBackgroundColor: "#2e59d9",
                borderColor: "#4e73df",
                data: [
                    {{ $siswa_per_kelas[1] ?? 0 }},
                    {{ $siswa_per_kelas[2] ?? 0 }},
                    {{ $siswa_per_kelas[3] ?? 0 }},
                    {{ $siswa_per_kelas[4] ?? 0 }},
                    {{ $siswa_per_kelas[5] ?? 0 }},
                    {{ $siswa_per_kelas[6] ?? 0 }},
                ],
            }],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: { legend: { display: false } }
        }
    });

    // --- Chart Absensi ---
    var ctxAbsensi = document.getElementById("chartAbsensi");
    var chartAbsensi = new Chart(ctxAbsensi, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($absensi_chart['labels']) !!},
            datasets: [{
                data: {!! json_encode($absensi_chart['data']) !!},
                backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#36b9cc'],
                hoverBackgroundColor: ['#17a673', '#be2617', '#dda20a', '#2c9faf'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        },
    });
</script>
@endpush
@endsection