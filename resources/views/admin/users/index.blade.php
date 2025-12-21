@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Manajemen User</h1>
            <p class="text-muted small mb-0">Kelola data guru, siswa, dan admin sekolah.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import CSV
            </button>
            <a href="{{ route('admin.users.export', request()->query()) }}" class="btn btn-sm btn-info text-white shadow-sm">
                <i class="bi bi-download me-1"></i> Export
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah User
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3" data-bs-toggle="collapse" data-bs-target="#collapseFilter" style="cursor: pointer;">
            <h6 class="m-0 fw-bold text-primary d-flex align-items-center">
                <i class="bi bi-funnel me-2"></i> Filter & Pencarian
                <i class="bi bi-chevron-down ms-auto text-gray-400"></i>
            </h6>
        </div>
        <div class="collapse show" id="collapseFilter">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="Nama / Email / NIP..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Kelas</label>
                            <select name="kelas" class="form-select form-select-sm">
                                <option value="">Semua Kelas</option>
                                @foreach(config('lms.daftar_kelas') as $k)
                                    <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Status</label>
                            <select name="is_active" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-grid w-100 gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="bi bi-funnel-fill"></i> Terapkan
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-2" id="bulkActionToolbar" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-secondary me-2"><span id="selectedCount">0</span> terpilih</span>
                
                <form action="{{ route('admin.users.bulk-delete') }}" method="POST" id="bulkDeleteForm" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div id="bulkDeleteInputs"></div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulkDelete()">
                        <i class="bi bi-trash me-1"></i> Hapus Terpilih
                    </button>
                </form>

                <div class="dropdown d-inline">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-toggle-on me-1"></i> Ubah Status
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <form action="{{ route('admin.users.bulk-toggle-active') }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_active" value="1">
                                <div id="bulkActiveInputs1"></div>
                                <button type="submit" class="dropdown-item small" onclick="prepareBulkInputs('bulkActiveInputs1')">
                                    <i class="bi bi-check-circle text-success me-2"></i> Aktifkan Semua
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('admin.users.bulk-toggle-active') }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_active" value="0">
                                <div id="bulkActiveInputs0"></div>
                                <button type="submit" class="dropdown-item small" onclick="prepareBulkInputs('bulkActiveInputs0')">
                                    <i class="bi bi-x-circle text-secondary me-2"></i> Nonaktifkan Semua
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" width="100%">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th width="3%" class="text-center px-3">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th width="5%" class="text-center">No</th>
                            <th width="30%">User</th>
                            <th width="15%">Role</th>
                            <th width="15%">Info Sekolah</th>
                            <th width="15%">Status</th>
                            <th width="17%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td class="text-center px-3">
                                
                                    <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}">
                            
                            </td>
                            <td class="text-center">{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role == 'admin')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill"><i class="bi bi-shield-lock me-1"></i> Admin</span>
                                @elseif($user->role == 'guru')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill"><i class="bi bi-person-workspace me-1"></i> Guru</span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill"><i class="bi bi-mortarboard me-1"></i> Siswa</span>
                                @endif
                            </td>
                            <td>
                                @if($user->role == 'guru')
                                    <small class="text-muted d-block">NIP:</small>
                                    <span class="font-monospace small">{{ $user->nip ?? '-' }}</span>
                                @elseif($user->role == 'siswa')
                                    <small class="text-muted d-block">Kelas {{ $user->kelas ?? '-' }}</small>
                                    <span class="font-monospace small text-muted">NISN: {{ $user->nisn ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm rounded-pill px-3 {{ $user->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }}" 
                                            style="font-size: 0.75rem;">
                                        @if($user->is_active)
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        @else
                                            <i class="bi bi-x-circle me-1"></i> Nonaktif
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-light btn-sm text-primary" data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-light btn-sm text-warning" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <button type="button" class="btn btn-light btn-sm text-dark" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#resetPassModal{{ $user->id }}" 
                                            title="Reset Password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    
                                    @if(auth()->id() !== $user->id)
                                    <button type="button" class="btn btn-light btn-sm text-danger delete-btn" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>

                                <div class="modal fade text-start" id="resetPassModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-key me-2"></i> Reset Password</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning py-2 mb-3 small">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                        Anda akan mengubah password untuk user: <strong>{{ $user->name }}</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Password Baru</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Konfirmasi Password</label>
                                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-primary">Simpan Password Baru</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-inbox fs-1 opacity-25"></i>
                                    <span class="mt-2">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 border-top d-flex align-items-center justify-content-between">
        <div class="small text-muted">
            Menampilkan 
            <span class="fw-bold">{{ $users->firstItem() ?? 0 }}</span> 
            sampai 
            <span class="fw-bold">{{ $users->lastItem() ?? 0 }}</span> 
            dari total 
            <span class="fw-bold">{{ $users->total() }}</span> 
            data
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Import User CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <strong>Format CSV:</strong><br>
                        name, email, password, role (guru/siswa), nisn, nip, kelas
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Role Default</label>
                        <select name="role" class="form-select" required>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                        <div class="form-text">Digunakan jika kolom role di CSV kosong.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">File CSV</label>
                        <input type="file" name="file" class="form-control" required accept=".csv">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- 1. LOGIC SELECT ALL & BULK ACTION ---
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionToolbar = document.getElementById('bulkActionToolbar');
    const selectedCountSpan = document.getElementById('selectedCount');

    function toggleToolbar() {
        const selected = document.querySelectorAll('.user-checkbox:checked').length;
        if (selected > 0) {
            bulkActionToolbar.style.display = 'block';
            selectedCountSpan.innerText = selected;
        } else {
            bulkActionToolbar.style.display = 'none';
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(cb => cb.checked = this.checked);
        toggleToolbar();
    });

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleToolbar);
    });

    // Helper: Masukkan ID yang dipilih ke dalam Form sebelum submit
    window.prepareBulkInputs = function(containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Reset
        document.querySelectorAll('.user-checkbox:checked').forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
    }

    // Logic Tombol Hapus Massal
    window.confirmBulkDelete = function() {
        Swal.fire({
            title: 'Hapus User Terpilih?',
            text: "User yang dipilih akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                prepareBulkInputs('bulkDeleteInputs');
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    }

    // --- 2. LOGIC TOOLTIP & SINGLE DELETE ---
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus User?',
                html: `Anda akan menghapus user <strong>${userName}</strong>.<br>Data ini tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteForm');
                    form.action = `/admin/users/${userId}`; 
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection