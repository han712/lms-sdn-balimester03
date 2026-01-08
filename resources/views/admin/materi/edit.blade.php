@extends('layouts.app')

@section('title', 'Edit Materi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Materi: {{ $materi->judul }}</h1>
        <a href="{{ route('admin.materi.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.materi.update', $materi->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Judul Materi</label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul', $materi->judul) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Deskripsi / Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="5">{{ old('keterangan', $materi->keterangan) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Kelas</label>
                                    <select name="kelas" class="form-select">
                                        @foreach(config('lms.daftar_kelas') as $k)
                                            <option value="{{ $k }}" {{ old('kelas', $materi->kelas) == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Tipe Konten</label>
                                    <select name="tipe" class="form-select">
                                        <option value="materi" {{ old('tipe', $materi->tipe) == 'materi' ? 'selected' : '' }}>Materi Pelajaran</option>
                                        <option value="kuis" {{ old('tipe', $materi->tipe) == 'kuis' ? 'selected' : '' }}>Kuis / Tugas</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Status Publikasi</label>
                                    <select name="is_published" class="form-select">
                                        <option value="1" {{ old('is_published', $materi->is_published) ? 'selected' : '' }}>Published (Tayang)</option>
                                        <option value="0" {{ !old('is_published', $materi->is_published) ? 'selected' : '' }}>Draft (Sembunyikan)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-primary fw-bold mb-3">Lampiran & Media</h6>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Link Referensi (Web)</label>
                            <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link', $materi->link) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Link Video (Youtube)</label>
                            <input type="url" name="video" class="form-control" placeholder="https://youtube.com/..." value="{{ old('video', $materi->video) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Ganti File Upload</label>
                            <input type="file" name="file" class="form-control">
                            @if($materi->file)
                                <small class="text-muted d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$materi->file) }}" target="_blank">Lihat File</a></small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection