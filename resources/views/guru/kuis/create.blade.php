@extends('layouts.guru')

@section('title', 'Buat Kuis Baru')

@section('content')
<div class="container-fluid">
    <form action="{{ route('guru.kuis.store') }}" method="POST" enctype="multipart/form-data" id="quizForm">
        @csrf
        
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Kuis</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Judul Kuis <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Kuis Matematika Perkalian Dasar" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" class="form-control" required>
                                <option value="">Pilih Kelas</option>
                                @foreach(range(1, 6) as $k)
                                    <option value="{{ $k }}">Kelas {{ $k }} SD</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>KKM / Passing Grade</label>
                            <input type="number" name="kkm" class="form-control" value="70">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi / Petunjuk Pengerjaan</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Kerjakan dengan teliti ya anak-anak!"></textarea>
                </div>
            </div>
        </div>

        <div id="soal-container">
            </div>

        <div class="card mb-5 bg-transparent border-0">
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-success btn-lg shadow-sm" onclick="addQuestion()">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Soal Baru
                </button>
                <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">
                    <i class="fas fa-save mr-2"></i> SIMPAN KUIS
                </button>
            </div>
        </div>
    </form>
</div>

<template id="soal-template">
    <div class="card shadow mb-4 border-left-info soal-item animate__animated animate__fadeIn">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-gray-100">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-question-circle mr-2 text-info"></i> Soal No. <span class="soal-number"></span>
            </h6>
            <button type="button" class="btn btn-danger btn-sm rounded-circle" onclick="removeQuestion(this)" title="Hapus Soal">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 border-right">
                    <div class="form-group">
                        <label class="font-weight-bold">Pertanyaan</label>
                        <textarea name="soal[INDEX][pertanyaan]" class="form-control" rows="3" required placeholder="Tulis pertanyaan di sini..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="btn btn-outline-primary btn-sm btn-block text-left" style="cursor: pointer;">
                            <i class="fas fa-image mr-2"></i> Upload Gambar (Opsional) - Biar Siswa Gak Bosen!
                            <input type="file" name="soal[INDEX][gambar]" class="d-none" onchange="previewImage(this)">
                        </label>
                        <div class="mt-2 img-preview-container d-none text-center p-2 bg-light rounded border border-dashed">
                            <img src="" class="img-fluid rounded" style="max-height: 200px;">
                            <br>
                            <small class="text-muted">Preview Gambar Soal</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Soal</label>
                        <select name="soal[INDEX][tipe]" class="form-control tipe-selector" onchange="toggleTipe(this)">
                            <option value="pilihan_ganda">Pilihan Ganda (PG)</option>
                            <option value="essay">Essay / Isian Singkat</option>
                        </select>
                    </div>

                    <div class="pg-area">
                        <label class="font-weight-bold small text-muted mb-2">Opsi Jawaban & Kunci</label>
                        
                        @foreach(['a', 'b', 'c', 'd'] as $opt)
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-white">
                                    <input type="radio" name="soal[INDEX][kunci]" value="{{ $opt }}" required>
                                    <strong class="ml-2 text-uppercase">{{ $opt }}</strong>
                                </div>
                            </div>
                            <input type="text" name="soal[INDEX][opsi][{{ $opt }}]" class="form-control" placeholder="Jawaban {{ strtoupper($opt) }}">
                        </div>
                        @endforeach
                    </div>

                    <div class="essay-area d-none">
                        <div class="form-group">
                            <label class="font-weight-bold">Kunci Jawaban Singkat</label>
                            <input type="text" name="soal[INDEX][kunci_essay]" class="form-control bg-light" placeholder="Jawaban yang benar...">
                            <small class="text-muted">Digunakan untuk auto-koreksi (opsional)</small>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label>Bobot Nilai</label>
                        <input type="number" name="soal[INDEX][bobot]" class="form-control" value="10">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    let soalIndex = 0;

    function addQuestion() {
        const container = document.getElementById('soal-container');
        const template = document.getElementById('soal-template').innerHTML;
        
        // Replace INDEX placeholder dengan current index
        const html = template.replace(/INDEX/g, soalIndex);
        
        // Append HTML (agak tricky kalau pake string, jadi pake insertAdjacentHTML)
        container.insertAdjacentHTML('beforeend', html);
        
        // Update nomor soal
        updateSoalNumbers();
        
        soalIndex++;
    }

    function removeQuestion(btn) {
        if(confirm('Hapus soal ini?')) {
            btn.closest('.soal-item').remove();
            updateSoalNumbers();
        }
    }

    function updateSoalNumbers() {
        const numbers = document.querySelectorAll('.soal-number');
        numbers.forEach((span, i) => {
            span.textContent = i + 1;
        });
    }

    function toggleTipe(select) {
        const cardBody = select.closest('.card-body');
        const pgArea = cardBody.querySelector('.pg-area');
        const essayArea = cardBody.querySelector('.essay-area');
        const radios = pgArea.querySelectorAll('input[type="radio"]');
        const pgInputs = pgArea.querySelectorAll('input[type="text"]');
        const essayInput = essayArea.querySelector('input');

        if (select.value === 'essay') {
            pgArea.classList.add('d-none');
            essayArea.classList.remove('d-none');
            
            // Disable PG inputs agar tidak required/dikirim
            radios.forEach(r => r.disabled = true);
            pgInputs.forEach(i => i.disabled = true);
            
            // Enable Essay input & ganti nama jadi 'kunci' agar controller membacanya sbg kunci
            essayInput.disabled = false;
            essayInput.name = essayInput.name.replace('kunci_essay', 'kunci');
        } else {
            pgArea.classList.remove('d-none');
            essayArea.classList.add('d-none');
            
            radios.forEach(r => r.disabled = false);
            pgInputs.forEach(i => i.disabled = false);
            
            essayInput.disabled = true;
            // Kembalikan nama temp agar tidak bentrok
            essayInput.name = essayInput.name.replace('kunci', 'kunci_essay');
        }
    }

    function previewImage(input) {
        const container = input.closest('.form-group').querySelector('.img-preview-container');
        const img = container.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            container.classList.add('d-none');
        }
    }

    // Tambah 1 soal otomatis saat load
    document.addEventListener('DOMContentLoaded', function() {
        addQuestion();
    });
</script>
<style>
    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }
    .input-group-text input[type="radio"] {
        cursor: pointer;
    }
</style>
@endpush
@endsection