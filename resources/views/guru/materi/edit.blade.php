@extends('layouts.guru')

@section('title', 'Edit Materi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Materi
        </h1>
        <a href="{{ route('guru.materi.show', $materi->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Informasi Materi</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.materi.update', $materi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Similar form fields as create.blade.php but with values -->
                        <div class="form-group">
                            <label for="judul">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" name="judul" 
                                   value="{{ old('judul', $materi->judul) }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $materi->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe">Tipe <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipe" name="tipe" required>
                                        <option value="materi" {{ $materi->tipe == 'materi' ? 'selected' : '' }}>Materi Pembelajaran</option>
                                        <option value="kuis" {{ $materi->tipe == 'kuis' ? 'selected' : '' }}>Kuis/Tugas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kelas">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-control" id="kelas" name="kelas" required>
                                        @foreach($kelasList as $k)
                                            <option value="{{ $k }}" {{ $materi->kelas == $k ? 'selected' : '' }}>
                                                Kelas {{ $k }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if($materi->file_path)
                        <div class="form-group">
                            <label>File Saat Ini:</label>
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file"></i> {{ basename($materi->file_path) }}
                                </div>
                                <div>
                                    <a href="{{ asset('storage/' . $materi->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <div class="custom-control custom-checkbox d-inline-block ml-2">
                                        <input type="checkbox" class="custom-control-input" id="remove_file" name="remove_file" value="1">
                                        <label class="custom-control-label" for="remove_file">Hapus file</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="file">Upload File Baru (Opsional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="file">Pilih file baru...</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_published" 
                                       name="is_published" value="1" {{ $materi->is_published ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_published">
                                    Publish materi
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Materi
                            </button>
                            <a href="{{ route('guru.materi.show', $materi->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Peringatan</h6>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Mengubah kelas akan mereset data absensi</li>
                        <li>Upload file baru akan mengganti file lama</li>
                        <li>Unpublish materi akan menyembunyikan dari siswa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Pilih file baru...';
    e.target.nextElementSibling.textContent = fileName;
});
</script>
@endpush
@endsection