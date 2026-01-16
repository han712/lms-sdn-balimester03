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
                        <span class="text-white-50 small">
                            <i class="bi bi-clock"></i> Diposting {{ $materi->created_at->diffForHumans() }}
                        </span>
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

                    @php
                        // konten teks utama
                        $content = $materi->keterangan ?? $materi->deskripsi ?? '';

                        // kumpulkan sumber url:
                        // 1) dari kolom DB (video & link)
                        // 2) dari konten teks (kalau user tempel url di keterangan)
                        $urls = [];

                        if (!empty($materi->video)) $urls[] = trim($materi->video);
                        if (!empty($materi->link))  $urls[] = trim($materi->link);

                        preg_match_all('/https?:\/\/[^\s<]+/i', $content, $matches);
                        if (!empty($matches[0])) {
                            foreach ($matches[0] as $u) $urls[] = $u;
                        }

                        // bersihkan duplikat
                        $urls = array_values(array_unique(array_filter($urls)));

                        // fungsi extract youtube id
                        $youtubeId = null;

                        foreach ($urls as $url) {
                            $u = trim($url);
                            $u = rtrim($u, ".)],");

                            // youtu.be/VIDEOID
                            if (preg_match('~youtu\.be/([a-zA-Z0-9_-]{11})~', $u, $m)) {
                                $youtubeId = $m[1];
                                break;
                            }

                            // youtube.com/watch?v=VIDEOID
                            if (preg_match('~youtube\.com/watch\?~', $u)) {
                                $query = parse_url($u, PHP_URL_QUERY);
                                parse_str($query ?? '', $q);
                                if (!empty($q['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $q['v'])) {
                                    $youtubeId = $q['v'];
                                    break;
                                }
                            }

                            // youtube.com/shorts/VIDEOID
                            if (preg_match('~youtube\.com/shorts/([a-zA-Z0-9_-]{11})~', $u, $m)) {
                                $youtubeId = $m[1];
                                break;
                            }

                            // youtube.com/embed/VIDEOID
                            if (preg_match('~youtube\.com/embed/([a-zA-Z0-9_-]{11})~', $u, $m)) {
                                $youtubeId = $m[1];
                                break;
                            }
                        }

                        // file lampiran
                        $filePath = $materi->file ?? $materi->file_path ?? null;
                    @endphp

                    {{-- Konten teks --}}
                    @if(trim($content) !== '')
                        <div class="mb-4 lh-lg text-dark">
                            {!! nl2br(e($content)) !!}
                        </div>
                    @endif

                    {{-- Embed YouTube dari DB --}}
                    @if($youtubeId)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Video</h6>
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
                                <iframe
                                    src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>

                            {{-- Link asli (opsional, biar bisa dibuka di app youtube) --}}
                            @if(!empty($materi->video))
                                <div class="mt-2">
                                    <a href="{{ $materi->video }}" target="_blank" class="small text-decoration-none">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka di YouTube
                                    </a>
                                </div>
                            @endif
                        </div>
                    @elseif(!empty($materi->video))
                        {{-- Kalau kolom video ada tapi formatnya bukan youtube --}}
                        <div class="alert alert-warning border-0 rounded-xl">
                            Link video tersimpan, tapi formatnya tidak terbaca sebagai link YouTube.
                            <div class="mt-2">
                                <a href="{{ $materi->video }}" target="_blank">
                                    {{ $materi->video }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Link tambahan (jika ada) --}}
                    @if(!empty($materi->link))
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Link Tambahan</h6>
                            <a href="{{ $materi->link }}" target="_blank" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-link-45deg"></i> Buka Link
                            </a>
                        </div>
                    @endif

                    {{-- Lampiran file --}}
                    @if($filePath)
                        <div class="card bg-light border-0 rounded-xl p-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text-fill text-primary fs-2 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Lampiran File</h6>
                                        <small class="text-muted">{{ basename($filePath) }}</small>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-download me-2"></i> Download
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- =======================
                 BAGIAN KUIS
            ======================== --}}
            @if($materi->tipe == 'kuis')

                @if(isset($jawabanKuis) && $jawabanKuis)
                    <div class="card border-0 shadow rounded-2xl mb-5">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="fw-bold text-dark">Kamu Sudah Mengerjakan!</h3>
                            <p class="text-muted">Dikirim pada: {{ $jawabanKuis->created_at->format('d M Y, H:i') }}</p>

                            @if($jawabanKuis->nilai !== null)
                                <div class="d-inline-block bg-light px-5 py-3 rounded-xl mt-3 border">
                                    <small class="text-uppercase fw-bold text-muted">Nilai Kamu</small>
                                    <div class="display-3 fw-bold {{ $jawabanKuis->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                        {{ $jawabanKuis->nilai }}
                                    </div>

                                    @if($jawabanKuis->catatan_guru)
                                        <div class="alert alert-info mt-2 mb-0 py-2 small">
                                            <strong>Catatan Guru:</strong> {{ $jawabanKuis->catatan_guru }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning d-inline-block rounded-pill px-4 mt-3">
                                    <i class="bi bi-hourglass-split me-2"></i> Menunggu dinilai oleh Guru
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow rounded-2xl mb-5">
                        <div class="card-header bg-white border-bottom border-light py-3">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-pencil-square text-warning me-2"></i> Kerjakan Kuis
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            @if(!isset($soals) || $soals->count() == 0)
                                <div class="alert alert-warning border-0 rounded-xl mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Soal belum tersedia untuk kuis ini.
                                </div>
                            @else
                                <form action="{{ route('siswa.materi.submit-kuis', $materi->id) }}" method="POST">
                                    @csrf

                                    @foreach($soals as $i => $s)
                                        <div class="mb-4">
                                            <div class="fw-bold mb-2">
                                                {{ $i+1 }}. {{ $s->pertanyaan }}
                                            </div>

                                            @if($s->gambar)
                                                <div class="mb-3">
                                                    <img
                                                        src="{{ asset('storage/' . $s->gambar) }}"
                                                        alt="Gambar Soal"
                                                        class="img-fluid rounded border"
                                                        style="max-height: 320px;"
                                                    >
                                                </div>
                                            @endif

                                            @if($s->tipe_soal === 'pilihan_ganda')
                                                @php
                                                    $opsi = is_array($s->opsi_jawaban) ? $s->opsi_jawaban : json_decode($s->opsi_jawaban, true);
                                                @endphp

                                                @if(is_array($opsi))
                                                    @foreach($opsi as $key => $val)
                                                        <div class="form-check mb-1">
                                                            <input
                                                                class="form-check-input"
                                                                type="radio"
                                                                name="jawaban[{{ $s->id }}]"
                                                                value="{{ $key }}"
                                                                id="soal{{ $s->id }}_{{ $key }}"
                                                                required
                                                            >
                                                            <label class="form-check-label" for="soal{{ $s->id }}_{{ $key }}">
                                                                {{ strtoupper($key) }}. {{ $val }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-muted small">Opsi jawaban tidak valid.</div>
                                                @endif
                                            @else
                                                <div class="form-group">
                                                    <textarea
                                                        name="jawaban[{{ $s->id }}]"
                                                        class="form-control"
                                                        rows="3"
                                                        placeholder="Tulis jawaban kamu di sini..."
                                                        required
                                                    ></textarea>
                                                    <small class="text-muted">Tipe soal: essay</small>
                                                </div>
                                            @endif
                                        </div>

                                        @if(!$loop->last)
                                            <hr>
                                        @endif
                                    @endforeach

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-warning btn-lg text-dark fw-bold rounded-pill">
                                            <i class="bi bi-send-fill me-2"></i> Kirim Jawaban
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

            @endif

        </div>
    </div>
</div>
@endsection