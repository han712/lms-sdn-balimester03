@extends('layouts.app')

@section('title', 'Semua Materi')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Monitoring Materi Pembelajaran</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.materi.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <select name="guru_id" class="form-control">
                            <option value="">Semua Guru</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ request('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="kelas" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach(range(1, 6) as $k)
                                <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="tipe" class="form-control">
                            <option value="">Semua Tipe</option>
                            <option value="materi" {{ request('tipe') == 'materi' ? 'selected' : '' }}>Materi</option>
                            <option value="kuis" {{ request('tipe') == 'kuis' ? 'selected' : '' }}>Kuis</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materi as $item)
                        <tr>
                            <td>{{ $loop->iteration + $materi->firstItem() - 1 }}</td>
                            <td>
                                <strong>{{ $item->judul }}</strong>
                                @if($item->file)
                                <br><small class="text-primary"><i class="fas fa-paperclip"></i> Ada File</small>
                                @endif
                            </td>
                            <td>{{ $item->guru->name ?? 'User Terhapus' }}</td>
                            <td>Kelas {{ $item->kelas }}</td>
                            <td>
                                @if($item->tipe == 'kuis') <span class="badge badge-warning">Kuis</span>
                                @else <span class="badge badge-info">Materi</span>
                                @endif
                            </td>
                            <td>
                                {!! $item->is_published ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-secondary">Draft</span>' !!}
                            </td>
                            <td>{{ $item->created_at->format('d/m/y') }}</td>
                            <td>
                                @if($item->file)
                                <a href="{{ asset('storage/'.$item->file) }}" target="_blank" class="btn btn-sm btn-info" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>
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
@endsection