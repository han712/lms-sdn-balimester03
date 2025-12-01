@extends('layouts.guru')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-user-cog"></i> Pengaturan Profil
    </h1>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Data Diri</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('guru.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nip">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                   id="nip" name="nip" value="{{ old('nip', auth()->user()->nip) }}" required>
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                            <small class="text-muted">Email tidak dapat diubah.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Profil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Ganti Password</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" name="new_password" required>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" 
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-key"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection