@extends('layouts.guru')

@section('title', 'Detail Materi')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-eye"></i> Detail Materi
        </h1>
        <div class="btn-group">
            <a href="{{ route('guru.materi.edit', $materi->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('guru.materi.toggle-publish', $materi->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-{{ $materi->is_published ? 'secondary' : 'success' }}">
                    <i class="fas fa-{{ $materi->is_published ? 'eye-slash' : 'eye' }}"></i>
                    {{ $materi->is_published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
            <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Info Materi -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Materi</h6>
                    <div>
                        @if($materi->tipe === 'kuis')
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clipboard-list"></i> KUIS
                            </span>
                        @else
                            <span class="badge badge-info badge-lg">
                                <i class="fas fa-book"></i> MATERI
                            </span>
                        @endif
                        @if($materi->is_published)
                            <span class="badge badge-success badge-lg">PUBLISHED</span>
                        @else
                            <span class="badge badge-secondary badge-lg">DRAFT</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $materi->judul }}</h4>
                    
                    <div class="mb-3">
                        <span class="badge badge-primary">Kelas {{ $materi->kelas }}</span>
                        <span class="text-muted">• Dibuat {{ $materi->created_at->diffForHumans() }}</span>
                    </div>

                    <h6 class="text-muted">Deskripsi:</h6>
                    <p class="text-justify">{{ $materi->deskripsi }}</p>

                    @if($materi->file_path)
                    <hr>
                    <h6 class="text-muted">File Materi:</h6>
                    <div class="alert alert-info d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-file fa-2x mr-3"></i>
                            <strong>{{ basename($materi->file_path) }}</strong>
                            @if($materi->file_size)
                                <br><small class="text-muted">
                                    {{ number_format($materi->file_size / 1024 / 1024, 2) }} MB
                                </small>
                            @endif
                        </div>
                        <a href="{{ asset('storage/' . $materi->file_path) }}" 
                           target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    @endif

                    @if($materi->tipe === 'kuis' && $materi->tanggal_deadline)
                    <hr>
                    <h6 class="text-muted">Deadline:</h6>
                    <div class="alert alert-{{ now()->gt($materi->tanggal_deadline) ? 'danger' : 'warning' }}">
                        <i class="fas fa-clock"></i>
                        {{ \Carbon\Carbon::parse($materi->tanggal_deadline)->format('d F Y, H:i') }}
                        @if(now()->gt($materi->tanggal_deadline))
                            <span class="badge badge-danger ml-2">TERLEWAT</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Absensi / Jawaban Section -->
            @if($materi->tipe === 'materi')
                <!-- Data Absensi -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-success">Data Absensi</h6>
                        <a href="{{ route('guru.materi.absensi', $materi->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-edit"></i> Kelola Absensi
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-3">
                                <div class="p-3 border rounded bg-success text-white">
                                    <h3>{{ $absensiStats->hadir ?? 0 }}</h3>
                                    <small>Hadir</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded bg-info text-white">
                                    <h3>{{ $absensiStats->izin ?? 0 }}</h3>
                                    <small>Izin</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded bg-warning text-white">
                                    <h3>{{ $absensiStats->sakit ?? 0 }}</h3>
                                    <small>Sakit</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded bg-danger text-white">
                                    <h3>{{ $absensiStats->alpha ?? 0 }}</h3>
                                    <small>Alpha</small>
                                </div>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Persentase Kehadiran</span>
                                <strong>{{ $persentaseKehadiran }}%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $persentaseKehadiran }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Recent Absensi -->
                        <h6 class="mt-4 mb-3">Absensi Terbaru:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($materi->absensi->take(5) as $abs)
                                    <tr>
                                        <td>{{ $abs->siswa->name }}</td>
                                        <td>
                                            <span class="badge badge-{{ $abs->status === 'hadir' ? 'success' : ($abs->status === 'alpha' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($abs->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $abs->waktu_akses ? $abs->waktu_akses->diffForHumans() : '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data absensi</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <!-- Data Kuis -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">Hasil Kuis</h6>
                        <a href="{{ route('guru.kuis.hasil', $materi->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-list"></i> Lihat Semua Jawaban
                        </a>
                    </div>
                    <div class="card-body">
                        @if($kuisStats)
<div class="row mb-4 animate__animated animate__fadeIn">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Mengumpulkan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kuisStats['total_jawaban'] }} Siswa</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sudah Dinilai</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kuisStats['sudah_dinilai'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Rata-rata Nilai</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kuisStats['rata_rata'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Min / Max Nilai</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $kuisStats['nilai_terendah'] }} / {{ $kuisStats['nilai_tertinggi'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trophy fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    @if($materi->tipe === 'materi')
                        <a href="{{ route('guru.materi.absensi', $materi->id) }}" class="btn btn-success btn-block mb-2">
                            <i class="fas fa-clipboard-check"></i> Kelola Absensi
                        </a>
                    @else
                        <a href="{{ route('guru.kuis.hasil', $materi->id) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-tasks"></i> Lihat Jawaban Kuis
                        </a>
                    @endif
                    
                    <a href="{{ route('guru.materi.edit', $materi->id) }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Materi
                    </a>
                    
                    <form action="{{ route('guru.materi.duplicate', $materi->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-copy"></i> Duplikat Materi
                        </button>
                    </form>

                    <hr>

                    <form action="{{ route('guru.materi.destroy', $materi->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Hapus Materi
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Informasi</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-user text-primary"></i>
                            <strong>Guru:</strong> {{ $materi->guru->name }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar text-success"></i>
                            <strong>Dibuat:</strong> {{ $materi->created_at->format('d M Y, H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-edit text-warning"></i>
                            <strong>Diupdate:</strong> {{ $materi->updated_at->format('d M Y, H:i') }}
                        </li>
                        @if($materi->views_count ?? false)
                        <li class="mb-2">
                            <i class="fas fa-eye text-info"></i>
                            <strong>Dilihat:</strong> {{ $materi->views_count }} kali
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@if($materi->tipe === 'kuis' && !empty($kuisStats['distribusi_nilai']))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('chartDistribusiNilai').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['A (90-100)', 'B (80-89)', 'C (70-79)', 'D (60-69)', 'E (<60)'],
        datasets: [{
            label: 'Jumlah Siswa',
            data: [
                {{ $kuisStats['distribusi_nilai']['A'] ?? 0 }},
                {{ $kuisStats['distribusi_nilai']['B'] ?? 0 }},
                {{ $kuisStats['distribusi_nilai']['C'] ?? 0 }},
                {{ $kuisStats['distribusi_nilai']['D'] ?? 0 }},
                {{ $kuisStats['distribusi_nilai']['E'] ?? 0 }}
            ],
            backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
@endif
@endsection
