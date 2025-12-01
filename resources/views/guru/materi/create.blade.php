@extends('layouts.guru')

@section('title', 'Buat Materi Baru')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus"></i> Buat Materi Baru
        </h1>
        <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Materi</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data" id="materiForm">
                        @csrf
                        
                        <!-- Judul -->
                        <div class="form-group">
                            <label for="judul">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" name="judul" 
                                   value="{{ old('judul') }}" 
                                   placeholder="Masukkan judul materi" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="5" 
                                      placeholder="Jelaskan tentang materi ini..." required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <span id="charCount">0</span>/5000 karakter
                            </small>
                        </div>

                        <!-- Tipe & Kelas -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe">Tipe <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tipe') is-invalid @enderror" 
                                            id="tipe" name="tipe" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="materi" {{ old('tipe') == 'materi' ? 'selected' : '' }}>
                                            Materi Pembelajaran
                                        </option>
                                        <option value="kuis" {{ old('tipe') == 'kuis' ? 'selected' : '' }}>
                                            Kuis/Tugas
                                        </option>
                                    </select>
                                    @error('tipe')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kelas">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kelas') is-invalid @enderror" 
                                            id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        @for($i = 1; $i <= 6; $i++)
                                            <option value="{{ $i }}" {{ old('kelas') == $i ? 'selected' : '' }}>
                                                Kelas {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deadline (untuk kuis) -->
                        <div class="form-group" id="deadlineGroup" style="display: none;">
                            <label for="tanggal_deadline">Tanggal Deadline</label>
                            <input type="datetime-local" class="form-control" 
                                   id="tanggal_deadline" name="tanggal_deadline" 
                                   value="{{ old('tanggal_deadline') }}">
                            <small class="form-text text-muted">
                                Kosongkan jika tidak ada deadline
                            </small>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group">
                            <label for="file">File Materi</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                       id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4,.avi">
                                <label class="custom-file-label" for="file">Pilih file...</label>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Format: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, MP4, AVI (Max: 50MB)
                            </small>
                            <!-- Preview -->
                            <div id="filePreview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="is_published" name="is_published" value="1" 
                                       {{ old('is_published') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_published">
                                    Publish materi (siswa dapat melihat materi)
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika tidak dicentang, materi akan disimpan sebagai draft
                            </small>
                        </div>

                        <!-- Actions -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Materi
                            </button>
                            <button type="submit" name="save_and_create" value="1" class="btn btn-success">
                                <i class="fas fa-plus"></i> Simpan & Buat Baru
                            </button>
                            <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i> Panduan
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Tips Membuat Materi:</h6>
                    <ul class="small">
                        <li>Gunakan judul yang jelas dan menarik</li>
                        <li>Jelaskan tujuan pembelajaran di deskripsi</li>
                        <li>Upload file pendukung yang relevan</li>
                        <li>Pastikan file tidak terlalu besar (max 50MB)</li>
                    </ul>

                    <hr>

                    <h6>Perbedaan Tipe:</h6>
                    <div class="small">
                        <strong>Materi Pembelajaran:</strong>
                        <p>Konten untuk dibaca/dipelajari siswa</p>
                        
                        <strong>Kuis/Tugas:</strong>
                        <p>Siswa dapat mengumpulkan jawaban yang perlu dinilai</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Character Counter
const deskripsi = document.getElementById('deskripsi');
const charCount = document.getElementById('charCount');

deskripsi.addEventListener('input', function() {
    charCount.textContent = this.value.length;
    if (this.value.length > 5000) {
        charCount.classList.add('text-danger');
    } else {
        charCount.classList.remove('text-danger');
    }
});

// Show/Hide Deadline based on Tipe
document.getElementById('tipe').addEventListener('change', function() {
    const deadlineGroup = document.getElementById('deadlineGroup');
    if (this.value === 'kuis') {
        deadlineGroup.style.display = 'block';
    } else {
        deadlineGroup.style.display = 'none';
    }
});

// File Input Label
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Pilih file...';
    const fileSize = e.target.files[0]?.size || 0;
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
    
    // Show preview
    if (e.target.files[0]) {
        document.getElementById('filePreview').style.display = 'block';
        document.getElementById('fileName').textContent = fileName;
        document.getElementById('fileSize').textContent = 
            ' (' + (fileSize / 1024 / 1024).toFixed(2) + ' MB)';
    }
});

// Form Validation
document.getElementById('materiForm').addEventListener('submit', function(e) {
    const judul = document.getElementById('judul').value;
    const deskripsi = document.getElementById('deskripsi').value;
    const tipe = document.getElementById('tipe').value;
    const kelas = document.getElementById('kelas').value;
    
    if (!judul || !deskripsi || !tipe || !kelas) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi (*)');
        return false;
    }
});
</script>
@endpush
@endsection