@extends('layouts.app')

@section('title', 'Perpustakaan Materi')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-gray-800">📚 Ruang Belajar</h1>
            <p class="text-muted">Cari pelajaran dan tugasmu di sini</p>
        </div>
        
        <form action="{{ route('siswa.materi.index') }}" method="GET" class="d-flex gap-2 bg-white p-2 rounded-xl shadow-sm">
            <input type="text" name="search" class="form-control border-0" placeholder="Cari judul..." value="{{ request('search') }}">
            <select name="tipe" class="form-select border-0 bg-light" style="width: 120px;">
                <option value="">Semua</option>
                <option value="materi" {{ request('tipe') == 'materi' ? 'selected' : '' }}>Materi</option>
                <option value="kuis" {{ request('tipe') == 'kuis' ? 'selected' : '' }}>Kuis</option>
            </select>
            <button type="submit" class="btn btn-primary rounded-lg"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="row g-4">
        @forelse($materi as $item)
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-xl hover-lift position-relative overflow-hidden">
                
                @if($item->tipe == 'kuis' && $item->is_submitted)
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-10 d-flex justify-content-center align-items-center z-index-1" style="pointer-events: none;">
                        <span class="badge bg-success fs-5 shadow rotate-n-15">✅ Selesai Dikerjakan</span>
                    </div>
                @endif

                <div class="card-header border-0 py-2 {{ $item->tipe == 'kuis' ? 'bg-warning' : 'bg-primary' }}"></div>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="badge {{ $item->tipe == 'kuis' ? 'bg-warning text-dark' : 'bg-primary' }}">
                            {{ $item->tipe == 'kuis' ? '📝 KUIS' : '📖 BACAAN' }}
                        </span>
                        <small class="text-muted"><i class="bi bi-calendar3"></i> {{ $item->created_at->format('d/m/Y') }}</small>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-2">{{ $item->judul }}</h5>
                    <p class="card-text text-muted small flex-grow-1">
                        {{ Str::limit($item->deskripsi ?? 'Tidak ada deskripsi singkat.', 80) }}
                    </p>
                    
                    <div class="d-flex align-items-center mt-3 pt-3 border-top">
                        <div class="avatar bg-light rounded-circle p-2 me-2">
                            <i class="bi bi-person-circle text-secondary"></i>
                        </div>
                        <small class="text-muted fw-bold">{{ $item->guru->name }}</small>
                    </div>

                    <a href="{{ route('siswa.materi.show', $item->id) }}" class="btn w-100 mt-3 rounded-pill fw-bold {{ $item->tipe == 'kuis' ? 'btn-outline-warning text-dark' : 'btn-outline-primary' }}">
                        Buka Sekarang <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 py-5 text-center">
            <div class="mb-3">
                <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted">Tidak ada materi ditemukan</h4>
            <a href="{{ route('siswa.materi.index') }}" class="btn btn-primary mt-2">Reset Pencarian</a>
        </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $materi->links() }}
    </div>
</div>
@endsection