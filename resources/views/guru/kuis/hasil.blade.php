@extends('layouts.guru')

@section('title', 'Hasil Kuis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Hasil Kuis: {{ $materi->judul }}</h1>
        <a href="{{ route('guru.materi.show', $materi->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengumpulan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_submit'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Rata-Rata Nilai</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rata_rata'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Jawaban Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Tanggal Kirim</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materi->jawabanKuis as $jawaban)
                        <tr>
                            <td>{{ $jawaban->siswa->name }}</td>
                            <td>
                                {{ $jawaban->created_at->format('d M Y H:i') }}
                                @if($materi->tanggal_selesai && $jawaban->created_at > $materi->tanggal_selesai)
                                    <span class="badge badge-danger">Telat</span>
                                @endif
                            </td>
                            <td>
                                @if($jawaban->nilai !== null)
                                    <span class="badge badge-success">Sudah Dinilai</span>
                                @else
                                    <span class="badge badge-warning">Menunggu Penilaian</span>
                                @endif
                            </td>
                            <td class="font-weight-bold">{{ $jawaban->nilai ?? '-' }}</td>
                            <td>
                                <a href="{{ route('guru.kuis.detail', $jawaban->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check-circle"></i> Periksa & Nilai
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada siswa yang mengumpulkan jawaban.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection