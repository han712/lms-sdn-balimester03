@extends('layouts.guru')

@section('title', 'Prediksi Remedial Siswa')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Prediksi Risiko Remedial (AI)</h1>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Input Data Siswa</h6>
            </div>
            <div class="card-body">
                
                {{-- Tampilkan Error --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Tampilkan Hasil Prediksi --}}
                @if(session('hasil'))
                    <div class="alert {{ session('hasil')['prediksi'] == 1 ? 'alert-danger' : 'alert-success' }} text-center">
                        <h4 class="alert-heading font-weight-bold">Hasil Analisis AI</h4>
                        <hr>
                        <h1 class="display-4 font-weight-bold">
                            {{ session('hasil')['label'] }}
                        </h1>
                        <p class="mb-0">Tingkat Keyakinan Model: <strong>{{ session('hasil')['confidence'] }}%</strong></p>
                    </div>
                @endif

                <form action="{{ route('guru.prediksi.process') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rata_nilai_kuis">Rata-rata Nilai Kuis (0-100)</label>
                            <input type="number" step="0.01" class="form-control" name="rata_nilai_kuis" required placeholder="Contoh: 75.5">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="total_kuis_dikerjakan">Total Kuis Dikerjakan</label>
                            <input type="number" class="form-control" name="total_kuis_dikerjakan" required placeholder="Contoh: 10">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="presentasi_kehadiran">Persentase Kehadiran (%)</label>
                            <input type="number" step="0.1" max="100" class="form-control" name="presentasi_kehadiran" required placeholder="Contoh: 90">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jumlah_tidak_hadir">Jumlah Tidak Hadir (Hari)</label>
                            <input type="number" class="form-control" name="jumlah_tidak_hadir" required placeholder="Contoh: 2">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
                        <i class="fas fa-search-plus mr-2"></i> Analisis Sekarang
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection