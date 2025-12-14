@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit User: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="roleSelect" class="form-control" required>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa" {{ $user->role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>

                <div id="guruFields" style="display: {{ $user->role == 'guru' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip" class="form-control" value="{{ old('nip', $user->nip) }}">
                    </div>
                </div>

                <div id="siswaFields" style="display: {{ $user->role == 'siswa' ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NISN</label>
                                <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $user->nisn) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas" class="form-control">
                                    <option value="">Pilih Kelas</option>
                                    @foreach(range(1, 6) as $k)
                                        <option value="{{ $k }}" {{ $user->kelas == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isActive">Status Aktif</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const roleSelect = document.getElementById('roleSelect');
    roleSelect.addEventListener('change', function() {
        const role = this.value;
        document.getElementById('guruFields').style.display = role === 'guru' ? 'block' : 'none';
        document.getElementById('siswaFields').style.display = role === 'siswa' ? 'block' : 'none';
    });
</script>
@endpush
@endsection