@extends('layouts.guru')

@section('title', 'Buat Materi Pembelajaran')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book-open"></i> Buat Materi Pembelajaran
        </h1>
        <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Materi</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data" id="materiForm">
                        @csrf
                        <input type="hidden" name="tipe" value="materi">

                        <div class="form-group">
                            <label for="judul">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" name="judul" value="{{ old('judul') }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Isi Materi / Keterangan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" name="keterangan" rows="6" required>{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kelas">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kelas') is-invalid @enderror" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                    @foreach(config('lms.daftar_kelas') as $k)
                                    <option value="{{ $k }}" {{ old('kelas') == $k ? 'selected' : '' }}>
                                        Kelas {{ $k }}
                                    </option>
                                    @endforeach
                                    </select>
                                    @error('kelas')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Tayang</label>
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           id="tanggal_mulai" name="tanggal_mulai" 
                                           value="{{ old('tanggal_mulai', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="file">File Materi (PDF/Word/PPT/Video)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                       id="file" name="file">
                                <label class="custom-file-label" for="file">Pilih file...</label>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Max: 50MB</small>
                        </div>

                        <div class="form-group">
                            <label>Link Youtube (Opsional)</label>
                            <input type="url" name="video" class="form-control" placeholder="https://youtube.com/..." value="{{ old('video') }}">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1" checked>
                                <label class="custom-control-label" for="is_published">Langsung Publish?</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("file").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush
@endsection