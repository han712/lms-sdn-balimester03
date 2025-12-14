@extends('layouts.guru')

@section('title', 'Daftar Materi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-folder"></i> Manajemen Materi</h1>
        <div>
            <a href="{{ route('guru.materi.create') }}" class="btn btn-primary shadow-sm mr-2">
                <i class="fas fa-plus"></i> Materi Belajar
            </a>
            <a href="{{ route('guru.kuis.create') }}" class="btn btn-warning shadow-sm">
                <i class="fas fa-pen"></i> Buat Kuis
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card py-2 border-left-primary">
                <div class="card-body d-flex justify-content-around">
                    <span>Total: <b>{{ $filterStats['total'] }}</b></span>
                    <span>Published: <b class="text-success">{{ $filterStats['published'] }}</b></span>
                    <span>Draft: <b class="text-secondary">{{ $filterStats['draft'] }}</b></span>
                    <span>Kuis: <b class="text-warning">{{ $filterStats['kuis'] }}</b></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Materi & Kuis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="materiTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Judul & Keterangan</th>
                            <th>Tipe</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materi as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->judul }}</strong><br>
                                <small class="text-muted">{{ Str::limit($item->keterangan, 60) }}</small>
                                @if($item->file)
                                    <br><small><i class="fas fa-paperclip"></i> {{ basename($item->file) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($item->tipe == 'kuis')
                                    <span class="badge badge-warning">Kuis</span>
                                @else
                                    <span class="badge badge-info">Materi</span>
                                @endif
                            </td>
                            <td>Kelas {{ $item->kelas }}</td>
                            <td>
                                <form action="{{ route('guru.materi.toggle-publish', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $item->is_published ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $item->is_published ? 'Published' : 'Draft' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('guru.materi.show', $item->id) }}" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('guru.materi.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    
                                    <form action="{{ route('guru.materi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-0"><i class="fas fa-trash"></i></button>
                                    </form>

                                    <form action="{{ route('guru.materi.duplicate', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Copy"><i class="fas fa-copy"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Belum ada data materi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $materi->links() }}
        </div>
    </div>
</div>
@endsection