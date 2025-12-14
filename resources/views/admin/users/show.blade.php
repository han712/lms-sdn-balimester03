@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="btn btn-circle btn-lg btn-primary" style="width: 80px; height: 80px; font-size: 30px; line-height: 70px;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'guru' ? 'success' : 'info') }} mb-2">
                        {{ ucfirst($user->role) }}
                    </span>
                    
                    <div class="text-left mt-4">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">Status</th>
                                <td>: {!! $user->is_active ? '<span class="text-success">Aktif</span>' : '<span class="text-danger">Nonaktif</span>' !!}</td>
                            </tr>
                            <tr>
                                <th>Bergabung</th>
                                <td>: {{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                            @if($user->role == 'guru')
                            <tr>
                                <th>NIP</th>
                                <td>: {{ $user->nip ?? '-' }}</td>
                            </tr>
                            @endif
                            @if($user->role == 'siswa')
                            <tr>
                                <th>NISN</th>
                                <td>: {{ $user->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td>: Kelas {{ $user->kelas ?? '-' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Aktivitas</h6>
                </div>
                <div class="card-body">
                    @if($user->role == 'guru')
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Materi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_materi'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Published</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['materi_published'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Kuis</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_kuis'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($user->role == 'siswa')
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-success text-white shadow">
                                <div class="card-body">
                                    Hadir
                                    <div class="text-white-50 small">{{ $stats['hadir'] ?? 0 }} kali</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-danger text-white shadow">
                                <div class="card-body">
                                    Tidak Hadir
                                    <div class="text-white-50 small">{{ $stats['tidak_hadir'] ?? 0 }} kali</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-info text-white shadow">
                                <div class="card-body">
                                    Kuis Dijawab
                                    <div class="text-white-50 small">{{ $stats['total_kuis_dijawab'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-warning text-white shadow">
                                <div class="card-body">
                                    Rata-rata Nilai
                                    <div class="text-white-50 small">{{ number_format($stats['rata_nilai'] ?? 0, 1) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-center text-muted mt-5">Tidak ada statistik khusus untuk Admin.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection