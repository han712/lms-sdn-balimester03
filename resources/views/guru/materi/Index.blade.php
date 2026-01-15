@extends('layouts.guru')

@section('title', 'Kelola Materi & Kuis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pembelajaran</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 border-bottom-0">
            <ul class="nav nav-tabs card-header-tabs" id="materiTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request('tipe') == 'materi' || !request('tipe') ? 'active font-weight-bold text-primary' : 'text-secondary' }}"
                       href="{{ route('guru.materi.index', ['tipe' => 'materi']) }}">
                        <i class="fas fa-book-open me-2"></i> Materi Pembelajaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('tipe') == 'kuis' ? 'active font-weight-bold text-primary' : 'text-secondary' }}"
                       href="{{ route('guru.materi.index', ['tipe' => 'kuis']) }}">
                        <i class="fas fa-pen-square me-2"></i> Kelola Kuis
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <form action="{{ route('guru.materi.index') }}" method="GET" class="d-flex">
                        <input type="hidden" name="tipe" value="{{ request('tipe', 'materi') }}">
                        <input type="text" name="search" class="form-control me-2" placeholder="Cari judul..." value="{{ request('search') }}">
                        <select name="kelas" class="form-control me-2" style="width: 150px;">
                            <option value="">Semua Kelas</option>
                            @foreach(range(1, 6) as $k)
                                <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    @if(request('tipe') == 'kuis')
                        <a href="{{ route('guru.kuis.create') }}" class="btn btn-success shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Kuis Baru
                        </a>
                    @else
                        <a href="{{ route('guru.materi.create') }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Upload Materi Baru
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Judul</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Tanggal Tayang</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materi as $item)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $item->judul }}</div>
                                <small class="text-muted">{{ Str::limit(strip_tags($item->keterangan), 50) }}</small>
                            </td>

                            {{-- ✅ BADGE KELAS (lebih kebaca) --}}
                            <td>
                                <span class="badge badge-kelas">
                                    <i class="fas fa-users me-1"></i> Kelas {{ $item->kelas }}
                                </span>
                            </td>

                            <td>
                                @if($item->is_published)
                                    <span class="badge badge-status badge-published">
                                        <i class="fas fa-check-circle me-1"></i> Published
                                    </span>
                                @else
                                    <span class="badge badge-status badge-draft">
                                        <i class="fas fa-pencil-alt me-1"></i> Draft
                                    </span>
                                @endif
                            </td>

                            <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</td>

                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @if($item->tipe == 'kuis')
                                        <a href="{{ route('guru.kuis.hasil', $item->id) }}" class="btn btn-sm btn-info text-white" title="Lihat Nilai">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <a href="{{ route('guru.kuis.edit', $item->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Kuis">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('guru.materi.show', $item->id) }}" class="btn btn-sm btn-info text-white" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('guru.materi.edit', $item->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Materi">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    <form action="{{ route('guru.materi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
                                Belum ada data {{ request('tipe') ?? 'materi' }} ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $materi->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ✅ CSS badge kelas biar kontras dan kebaca --}}
<style>
    .badge-kelas {
        background-color: #e0f2fe; /* biru muda */
        color: #075985;           /* biru tua */
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(7, 89, 133, .15);
    }
</style>
@endsection