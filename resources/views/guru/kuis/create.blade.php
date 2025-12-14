@extends('layouts.guru')

@section('title', 'Buat Kuis Baru')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-pen-square"></i> Buat Kuis / Tugas
        </h1>
        <a href="{{ route('guru.kuis.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Batal
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Form Kuis</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.kuis.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group">
                            <label>Judul Kuis <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>

                        <div class="form-group">
                            <label>Instruksi / Soal</label>
                            <textarea class="form-control" name="keterangan" rows="4" placeholder="Tulis soal atau instruksi pengerjaan..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kelas</label>
                                    <select class="form-control" name="kelas" required>
                                        @for($i=1; $i<=6; $i++)
                                            <option value="{{ $i }}">Kelas {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="datetime-local" class="form-control" name="tanggal_mulai" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Batas Waktu (Deadline)</label>
                                    <input type="datetime-local" class="form-control" name="tanggal_selesai" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>File Soal (Opsional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file">
                                <label class="custom-file-label" for="file">Upload PDF/Doc...</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-save"></i> Buat Kuis
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection