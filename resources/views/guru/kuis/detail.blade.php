@extends('layouts.guru')

@section('title', 'Penilaian Kuis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star-half-alt"></i> Penilaian Kuis
        </h1>
        <a href="{{ route('guru.kuis.hasil', $jawaban->materi_id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        {{-- KIRI: Detail --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Jawaban</h6>
                    <small class="text-muted">
                        Dikirim: {{ $jawaban->created_at ? $jawaban->created_at->format('d F Y, H:i') : '-' }}
                    </small>
                </div>

                <div class="card-body">
                    {{-- Identitas siswa --}}
                    <div class="media mb-4 align-items-center">
                        <div class="icon-circle bg-primary mr-3" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:50%">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="media-body">
                            <h5 class="mt-0 mb-1 font-weight-bold">{{ $jawaban->siswa->name ?? '-' }}</h5>
                            <span class="text-muted">
                                Kelas {{ $jawaban->siswa->kelas ?? '-' }} • NISN: {{ $jawaban->siswa->nisn ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    {{-- PETUNJUK --}}
                    <div class="mb-4">
                        <label class="font-weight-bold text-gray-800">Petunjuk / Deskripsi Kuis:</label>
                        <div class="p-3 bg-light rounded border">
                            {{ $jawaban->materi->keterangan ?? $jawaban->materi->deskripsi ?? '-' }}
                        </div>
                    </div>

                    {{-- SOAL --}}
                    <div class="mb-4">
                        <label class="font-weight-bold text-gray-800">Soal:</label>

                        @php
                            // pastikan controller sudah load: $jawaban->load(['materi.soals', 'siswa']);
                            $soals = $jawaban->materi->soals ?? collect();

                            // jawaban siswa disimpan dalam kolom "jawaban" (json/array) pada tabel jawaban_kuis
                            // format yang kamu simpan: [soal_id => ['tipe'=>.., 'jawaban'=>.., 'kunci'=>.., 'bobot'=>..]]
                            // kalau data lama, bisa jadi null / string json
                            $jawabanJson = $jawaban->jawaban ?? null;
                            if (is_string($jawabanJson)) {
                                $decoded = json_decode($jawabanJson, true);
                                $jawabanMap = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($jawabanJson)) {
                                $jawabanMap = $jawabanJson;
                            } else {
                                $jawabanMap = [];
                            }
                        @endphp

                        @if($soals->count() > 0)
                            <div class="p-3 bg-white rounded border">
                                @foreach($soals as $idx => $soal)
                                    @php
                                        $sid = $soal->id;

                                        // ambil jawaban siswa per soal (kalau ada)
                                        $ansRow = $jawabanMap[$sid] ?? null;

                                        // jawaban siswa bisa tersimpan sebagai:
                                        // - string (misal "a" / "jawaban essay")
                                        // - array ['jawaban'=>...]
                                        $jawabanSiswa = null;
                                        if (is_array($ansRow)) {
                                            $jawabanSiswa = $ansRow['jawaban'] ?? null;
                                        } else {
                                            $jawabanSiswa = $ansRow;
                                        }

                                        // opsi jawaban PG
                                        $opsi = is_array($soal->opsi_jawaban)
                                            ? $soal->opsi_jawaban
                                            : json_decode($soal->opsi_jawaban ?? '[]', true);

                                        $kunci = $soal->kunci_jawaban ?? null;

                                        // status benar/salah untuk PG (essay tidak auto)
                                        $isBenar = null;
                                        if ($soal->tipe_soal === 'pilihan_ganda' && $jawabanSiswa !== null && $kunci !== null) {
                                            $isBenar = strtoupper(trim($jawabanSiswa)) === strtoupper(trim($kunci));
                                        }
                                    @endphp

                                    <div class="mb-4">
                                        <div class="font-weight-bold">
                                            {{ $idx+1 }}. {{ $soal->pertanyaan }}
                                            <span class="badge badge-secondary ml-2">{{ $soal->tipe_soal }}</span>
                                            <span class="badge badge-light ml-1">Bobot: {{ $soal->bobot_nilai }}</span>
                                        </div>

                                        {{-- gambar soal --}}
                                        @if($soal->gambar)
                                            <div class="my-2">
                                                <img
                                                    src="{{ asset('storage/' . $soal->gambar) }}"
                                                    alt="Gambar soal"
                                                    class="img-fluid rounded border"
                                                    style="max-height: 320px;"
                                                >
                                            </div>
                                        @endif

                                        {{-- tampil opsi PG --}}
                                        @if($soal->tipe_soal === 'pilihan_ganda')
                                            @if(is_array($opsi))
                                                <div class="small text-muted mb-2">Opsi:</div>
                                                <ul class="mb-2">
                                                    @foreach($opsi as $k => $v)
                                                        <li>{{ strtoupper($k) }}. {{ $v }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <div class="d-flex flex-wrap align-items-center">
                                                <div class="mr-3">
                                                    <span class="text-muted">Kunci:</span>
                                                    <b>{{ $kunci ? strtoupper($kunci) : '-' }}</b>
                                                </div>

                                                <div class="mr-3">
                                                    <span class="text-muted">Jawaban siswa:</span>
                                                    <b>{{ $jawabanSiswa !== null ? strtoupper($jawabanSiswa) : '-' }}</b>
                                                </div>

                                                @if($isBenar === true)
                                                    <span class="badge badge-success">Benar</span>
                                                @elseif($isBenar === false)
                                                    <span class="badge badge-danger">Salah</span>
                                                @else
                                                    <span class="badge badge-secondary">Belum ada jawaban</span>
                                                @endif
                                            </div>

                                        {{-- essay --}}
                                        @else
                                            <div class="mt-2">
                                                <div class="text-muted small mb-1">Jawaban siswa (Essay):</div>
                                                <div class="p-3 border rounded bg-light">
                                                    {!! nl2br(e($jawabanSiswa ?? '-')) !!}
                                                </div>

                                                <div class="text-muted small mt-2">
                                                    Kunci (opsional): <b>{{ $kunci ?? '-' }}</b>
                                                    <span class="ml-2">(Essay dinilai manual oleh guru)</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">Soal belum tersedia / belum ter-load.</div>
                        @endif
                    </div>

                    {{-- JAWABAN FILE (kalau ada skenario upload) --}}
                    @php
                        // beberapa project pakai jawaban_file, sebagian pakai file_path.
                        $file = $jawaban->jawaban_file ?? $jawaban->file_path ?? null;
                    @endphp

                    @if($file)
                        <div class="mb-3">
                            <label class="font-weight-bold text-gray-800">Lampiran Jawaban (File):</label>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-file-alt fa-2x align-middle mr-2"></i>
                                <span class="align-middle font-weight-bold">{{ basename($file) }}</span>
                                <a href="{{ asset('storage/' . $file) }}" target="_blank" class="btn btn-light btn-sm float-right mt-1">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- KANAN: Form Penilaian --}}
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
                            <input
                                type="number"
                                class="form-control form-control-lg text-center font-weight-bold @error('nilai') is-invalid @enderror"
                                id="nilai"
                                name="nilai"
                                min="0"
                                max="100"
                                value="{{ old('nilai', $jawaban->nilai) }}"
                                required
                                style="font-size: 24px; color: #4e73df;"
                            >
                            @error('nilai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="catatan_guru">Catatan / Feedback</label>
                            <textarea
                                class="form-control"
                                id="catatan_guru"
                                name="catatan_guru"
                                rows="4"
                                placeholder="Berikan masukan untuk siswa..."
                            >{{ old('catatan_guru', $jawaban->catatan_guru) }}</textarea>
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