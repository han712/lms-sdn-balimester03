@extends('layouts.app')

@section('title', $materi->judul)

@section('content')
<div class="container py-4">
    
    <div class="mb-4">
        <a href="{{ route('siswa.materi.index') }}" class="text-decoration-none text-muted fw-bold">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card border-0 shadow rounded-2xl overflow-hidden mb-4">
                <div class="p-4 p-md-5 text-white {{ $materi->tipe == 'kuis' ? 'bg-gradient-warning' : 'bg-gradient-primary' }}">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-white text-dark me-2">{{ strtoupper($materi->tipe) }}</span>
                        <span class="text-white-50 small"><i class="bi bi-clock"></i> Diposting {{ $materi->created_at->diffForHumans() }}</span>
                    </div>
                    <h1 class="display-6 fw-bold mb-2">{{ $materi->judul }}</h1>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span>Guru: {{ $materi->guru->name }}</span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-xl d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0 text-success">Kehadiran Tercatat!</h6>
                            <small class="text-dark">Sistem telah mencatat kamu membaca materi ini.</small>
                        </div>
                    </div>

                    <div class="mb-5 lh-lg text-dark">
                        {!! nl2br(e($materi->deskripsi ?? $materi->keterangan)) !!}
                    </div>

                    @if($materi->file_path)
                    <div class="card bg-light border-0 rounded-xl p-3 mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text-fill text-primary fs-2 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-0">Lampiran File</h6>
                                    <small class="text-muted">Klik tombol untuk mengunduh</small>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $materi->file_path) }}" target="_blank" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-download me-2"></i> Download
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($materi->tipe == 'kuis')
                <div class="card border-0 shadow rounded-2xl mb-5">
                    <div class="card-header bg-white border-bottom border-light py-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-pencil-square text-warning me-2"></i> Lembar Jawab Tugas
                        </h5>
                    </div>
                    <div class="card-body p-4">

                        @if($existingAnswer)
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3 class="fw-bold text-dark">Kamu Sudah Mengumpulkan!</h3>
                                <p class="text-muted">Dikirim pada: {{ $existingAnswer->created_at->format('d M Y, H:i') }}</p>
                                
                                @if($existingAnswer->nilai !== null)
                                    <div class="d-inline-block bg-light px-5 py-3 rounded-xl mt-3 border">
                                        <small class="text-uppercase fw-bold text-muted">Nilai Kamu</small>
                                        <div class="display-3 fw-bold {{ $existingAnswer->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                            {{ $existingAnswer->nilai }}
                                        </div>
                                        @if($existingAnswer->catatan_guru)
                                            <div class="alert alert-info mt-2 mb-0 py-2 small">
                                                <strong>Catatan Guru:</strong> {{ $existingAnswer->catatan_guru }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning d-inline-block rounded-pill px-4">
                                        <i class="bi bi-hourglass-split me-2"></i> Menunggu dinilai oleh Guru
                                    </div>
                                @endif
                            </div>

                        @else
                            <div class="alert alert-info border-0 rounded-xl mb-4">
                                <i class="bi bi-info-circle-fill me-2"></i> Silakan kerjakan tugas sesuai instruksi di atas, lalu upload file jawaban di sini.
                            </div>

                            <form action="{{ route('siswa.materi.submit-kuis', $materi->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Upload File Jawaban <span class="text-danger">*</span></label>
                                    <input type="file" name="file_jawaban" class="form-control form-control-lg" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <div class="form-text">Format: PDF, Word, atau Gambar (Max 5MB).</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Pesan untuk guru..."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg text-dark fw-bold rounded-pill">
                                        <i class="bi bi-send-fill me-2"></i> Kirim Jawaban
                                    </button>
                                </div>
                            </form>
                        @endif

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection