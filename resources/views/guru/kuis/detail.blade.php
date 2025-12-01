@extends('layouts.guru')

@section('title', 'Penilaian Kuis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star-half-alt"></i> Penilaian
        </h1>
        <a href="{{ route('guru.kuis.hasil', $jawaban->materi_id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Jawaban</h6>
                    <small class="text-muted">Dikirim: {{ $jawaban->created_at->format('d F Y, H:i') }}</small>
                </div>
                <div class="card-body">
                    <div class="media mb-4 align-items-center">
                        <div class="icon-circle bg-primary mr-3" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:50%">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="media-body">
                            <h5 class="mt-0 mb-1 font-weight-bold">{{ $jawaban->siswa->name }}</h5>
                            <span class="text-muted">Kelas {{ $jawaban->siswa->kelas }} • NISN: {{ $jawaban->siswa->nisn }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label class="font-weight-bold text-gray-800">Instruksi/Soal:</label>
                        <div class="p-3 bg-light rounded border">
                            {{ $jawaban->materi->deskripsi }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Jawaban Siswa:</label>
                        
                        @if($jawaban->file_path)
                            <div class="alert alert-info">
                                <i class="fas fa-file-alt fa-2x align-middle mr-2"></i>
                                <span class="align-middle font-weight-bold">{{ basename($jawaban->file_path) }}</span>
                                <a href="{{ asset('storage/' . $jawaban->file_path) }}" target="_blank" class="btn btn-light btn-sm float-right mt-1">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        @endif

                        @if($jawaban->jawaban_text)
                            <div class="p-3 border rounded bg-white">
                                {!! nl2br(e($jawaban->jawaban_text)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Form Penilaian</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.kuis.nilai', $jawaban->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="nilai">Nilai (0-100) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg text-center font-weight-bold @error('nilai') is-invalid @enderror" 
                                   id="nilai" name="nilai" min="0" max="100" 
                                   value="{{ old('nilai', $jawaban->nilai) }}" required
                                   style="font-size: 24px; color: #4e73df;">
                            @error('nilai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="feedback">Catatan / Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4" 
                                      placeholder="Berikan masukan untuk siswa...">{{ old('feedback', $jawaban->catatan_guru) }}</textarea>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-check-circle"></i> Simpan Nilai
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection