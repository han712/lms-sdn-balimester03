@extends('layouts.guru')

@section('title', 'Hasil Kuis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-poll"></i> Hasil Kuis
        </h1>
        <a href="{{ route('guru.materi.show', $materi->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body border-left-warning">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="font-weight-bold text-gray-800">{{ $materi->judul }}</h5>
                    <p class="mb-0 text-muted">Deadline: {{ $materi->tanggal_deadline ? \Carbon\Carbon::parse($materi->tanggal_deadline)->format('d M Y, H:i') : 'Tidak ada deadline' }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <span class="h2 font-weight-bold text-primary">{{ $jawaban->whereNotNull('nilai')->count() }}</span>
                    <span class="text-gray-500">/ {{ $jawaban->count() }} Dinilai</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengumpulan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Waktu Kirim</th>
                            <th>File/Jawaban</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jawaban as $index => $jw)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $jw->siswa->name }}</strong>
                                <br><small class="text-muted">{{ $jw->siswa->nisn }}</small>
                            </td>
                            <td>
                                {{ $jw->created_at->format('d M Y, H:i') }}
                                @if($materi->tanggal_deadline && $jw->created_at->gt($materi->tanggal_deadline))
                                    <span class="badge badge-danger">Terlambat</span>
                                @endif
                            </td>
                            <td>
                                @if($jw->file_path)
                                    <a href="{{ asset('storage/' . $jw->file_path) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Unduh File
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" 
                                            data-toggle="modal" data-target="#textModal{{ $jw->id }}">
                                        <i class="fas fa-align-left"></i> Lihat Teks
                                    </button>

                                    <div class="modal fade" id="textModal{{ $jw->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Jawaban {{ $jw->siswa->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ $jw->jawaban_text }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="font-weight-bold text-center">
                                {{ $jw->nilai ?? '-' }}
                            </td>
                            <td>
                                @if($jw->nilai !== null)
                                    <span class="badge badge-success">Dinilai</span>
                                @else
                                    <span class="badge badge-warning">Belum Dinilai</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('guru.kuis.detail', $jw->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Nilai
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Belum ada siswa yang mengumpulkan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection