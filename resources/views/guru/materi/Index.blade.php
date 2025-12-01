@extends('layouts.guru')

@section('title', 'Daftar Materi')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book"></i> Daftar Materi
        </h1>
        <a href="{{ route('guru.materi.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Buat Materi Baru
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Pencarian</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('guru.materi.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Pencarian</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari judul atau deskripsi..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Kelas</label>
                            <select name="kelas" class="form-control">
                                <option value="">Semua Kelas</option>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ request('kelas') == $i ? 'selected' : '' }}>
                                        Kelas {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tipe</label>
                            <select name="tipe" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="materi" {{ request('tipe') == 'materi' ? 'selected' : '' }}>Materi</option>
                                <option value="kuis" {{ request('tipe') == 'kuis' ? 'selected' : '' }}>Kuis</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Quick Stats -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="d-flex gap-3">
                        <div class="badge badge-primary badge-pill p-2">
                            Total: {{ $filterStats['total'] }}
                        </div>
                        <div class="badge badge-success badge-pill p-2">
                            Published: {{ $filterStats['published'] }}
                        </div>
                        <div class="badge badge-secondary badge-pill p-2">
                            Draft: {{ $filterStats['draft'] }}
                        </div>
                        <div class="badge badge-info badge-pill p-2">
                            Materi: {{ $filterStats['materi'] }}
                        </div>
                        <div class="badge badge-warning badge-pill p-2">
                            Kuis: {{ $filterStats['kuis'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="card shadow mb-4" id="bulkActionsCard" style="display: none;">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span id="selectedCount">0</span> materi dipilih
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('publish')">
                        <i class="fas fa-check"></i> Publish
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('unpublish')">
                        <i class="fas fa-minus"></i> Unpublish
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Materi List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Materi ({{ $materi->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="materiTable">
                    <thead class="bg-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Judul</th>
                            <th width="100">Tipe</th>
                            <th width="80">Kelas</th>
                            <th width="100">Status</th>
                            <th width="120">Statistik</th>
                            <th width="120">Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materi as $item)
                        <tr>
                            <td>
                                <input type="checkbox" class="checkbox-item" value="{{ $item->id }}">
                            </td>
                            <td>
                                <strong>{{ $item->judul }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ Str::limit($item->deskripsi, 80) }}
                                </small>
                                @if($item->file_path)
                                <br>
                                <small>
                                    <i class="fas fa-paperclip text-primary"></i>
                                    {{ basename($item->file_path) }}
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($item->tipe === 'kuis')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clipboard-list"></i> Kuis
                                    </span>
                                @else
                                    <span class="badge badge-info">
                                        <i class="fas fa-book"></i> Materi
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary">Kelas {{ $item->kelas }}</span>
                            </td>
                            <td>
                                @if($item->is_published)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Published
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-clock"></i> Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item->tipe === 'kuis')
                                    <small>
                                        <i class="fas fa-users text-info"></i> {{ $item->jawaban_kuis_count }} Jawaban
                                        <br>
                                        <i class="fas fa-exclamation-circle text-warning"></i> 
                                        {{ $item->jawaban_belum_dinilai_count }} Belum Dinilai
                                    </small>
                                @else
                                    <small>
                                        <i class="fas fa-user-check text-success"></i> 
                                        {{ $item->absensi_hadir_count }}/{{ $item->absensi_count }} Hadir
                                    </small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $item->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('guru.materi.show', $item->id) }}" 
                                       class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('guru.materi.edit', $item->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-secondary" 
                                            onclick="duplicateMateri({{ $item->id }})" title="Duplikat">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deleteMateri({{ $item->id }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada materi</p>
                                <a href="{{ route('guru.materi.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Materi Baru
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $materi->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Bulk Action Forms -->
<form id="bulkPublishForm" action="{{ route('guru.materi.bulk-publish') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkAction">
    <input type="hidden" name="ids" id="bulkIds">
</form>

<form id="bulkDeleteForm" action="{{ route('guru.materi.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Select All Checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.checkbox-item');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Individual Checkbox
document.querySelectorAll('.checkbox-item').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.checkbox-item:checked');
    const bulkCard = document.getElementById('bulkActionsCard');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkCard.style.display = 'block';
        selectedCount.textContent = selected.length;
    } else {
        bulkCard.style.display = 'none';
    }
}

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.checkbox-item:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal 1 materi', 'warning');
        return;
    }
    
    const actionText = action === 'publish' ? 'publish' : 'unpublish';
    
    Swal.fire({
        title: 'Konfirmasi',
        text: `${actionText} ${selected.length} materi yang dipilih?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('bulkAction').value = action;
            document.getElementById('bulkIds').value = JSON.stringify(selected);
            document.getElementById('bulkPublishForm').submit();
        }
    });
}

function bulkDelete() {
    const selected = Array.from(document.querySelectorAll('.checkbox-item:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal 1 materi', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Hapus ${selected.length} materi yang dipilih? Tindakan ini tidak dapat dibatalkan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('bulkDeleteIds').value = JSON.stringify(selected);
            document.getElementById('bulkDeleteForm').submit();
        }
    });
}

function deleteMateri(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Yakin ingin menghapus materi ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/guru/materi/${id}`;
            form.submit();
        }
    });
}

function duplicateMateri(id) {
    Swal.fire({
        title: 'Duplikat Materi',
        text: 'Buat salinan dari materi ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Duplikat',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/guru/materi/${id}/duplicate`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
