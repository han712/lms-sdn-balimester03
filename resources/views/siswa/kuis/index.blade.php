@extends('siswa.layouts.app')

@section('title', 'Riwayat Kuis')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clipboard-check"></i> Riwayat Kuis
        </h1>
        <a href="{{ route('siswa.materi.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Kuis
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['total_kuis'] }}
                        </div>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-clipboard"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Sudah Dinilai
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['sudah_dinilai'] }}
                        </div>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Belum Dinilai
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['belum_dinilai'] }}
                        </div>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Rata-rata
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($stats['rata_rata'], 1) }}
                        </div>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Summary -->
    @if($stats['sudah_dinilai'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-success mb-0">{{ $stats['nilai_tertinggi'] }}</h3>
                            <small class="text-muted">Nilai Tertinggi</small>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-primary mb-0">{{ number_format($stats['rata_rata'], 1) }}</h3>
                            <small class="text-muted">Rata-rata Nilai</small>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-danger mb-0">{{ $stats['nilai_terendah'] }}</h3>
                            <small class="text-muted">Nilai Terendah</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Penilaian</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="dinilai" {{ request('status') == 'dinilai' ? 'selected' : '' }}>
                            Sudah Dinilai
                        </option>
                        <option value="belum_dinilai" {{ request('status') == 'belum_dinilai' ? 'selected' : '' }}>
                            Belum Dinilai
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="mapel" class="form-select">
                        <option value="">Semua Mapel</option>
                        @foreach(\App\Models\Materi::getMapelOptions() as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>
                                {{ $mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <a href="{{ route('siswa.riwayat-kuis') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Kuis List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Riwayat Kuis</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Materi</th>
                            <th>Mapel</th>
                            <th>Tanggal Pengumpulan</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kuisList as $index => $kuis)
                        <tr>
                            <td>{{ $kuisList->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $kuis->materi->judul }}</strong><br>
                                <small class="text-muted">
                                    oleh {{ $kuis->materi->guru->name }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $kuis->materi->mapel_color }}">
                                    {{ $kuis->materi->mapel }}
                                </span>
                            </td>
                            <td>
                                {{ $kuis->created_at->format('d M Y') }}<br>
                                <small class="text-muted">
                                    {{ $kuis->created_at->format('H:i') }}
                                </small>
                            </td>
                            <td>
                                @if($kuis->nilai !== null)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Dinilai
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock"></i> Menunggu
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($kuis->nilai !== null)
                                    <h5 class="mb-0">
                                        <span class="badge 
                                            @if($kuis->nilai >= 80) bg-success
                                            @elseif($kuis->nilai >= 60) bg-primary
                                            @else bg-danger
                                            @endif">
                                            {{ $kuis->nilai }}
                                        </span>
                                    </h5>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('siswa.materi.show', $kuis->materi) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ asset('storage/' . $kuis->jawaban_file) }}" 
                                   class="btn btn-sm btn-outline-success" 
                                   target="_blank"
                                   title="Lihat Jawaban">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">
                                    Belum ada riwayat kuis
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($kuisList->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $kuisList->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
</style>
@endpush