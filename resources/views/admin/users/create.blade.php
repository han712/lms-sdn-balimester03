@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah User Baru</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form User</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Role <span class="text-danger">*</span></label>
                    <select name="role" id="roleSelect" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="">Pilih Role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div id="guruFields" style="display: none;">
                    <div class="form-group">
                        <label>NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                        @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div id="siswaFields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NISN <span class="text-danger">*</span></label>
                                <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn') }}">
                                @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelas <span class="text-danger">*</span></label>
                                <select name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                                    <option value="">Pilih Kelas</option>
                                    @foreach(range(1, 6) as $k)
                                        <option value="{{ $k }}" {{ old('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                    @endforeach
                                </select>
                                @error('kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="isActive">Aktifkan User Ini?</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Simpan User</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    const guruFields = document.getElementById('guruFields');
    const siswaFields = document.getElementById('siswaFields');

    function toggleFields() {
        const role = roleSelect.value;
        guruFields.style.display = role === 'guru' ? 'block' : 'none';
        siswaFields.style.display = role === 'siswa' ? 'block' : 'none';
    }

    roleSelect.addEventListener('change', toggleFields);
    // Run on load in case of validation error (old input)
    toggleFields();
</script>
@endpush
@endsection