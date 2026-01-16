@extends('layouts.app') 

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-person-circle me-2"></i> Profil Saya
        </h1>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-xl">
                <div class="card-body text-center p-5">
                    <!-- Avatar -->
                    <div class="mb-3 position-relative d-inline-block">
                        @if($siswa->avatar)
                            <img src="{{ asset('storage/' . $siswa->avatar) }}" 
                                 class="rounded-circle shadow-sm" 
                                 width="150" 
                                 height="150"
                                 style="object-fit: cover;"
                                 alt="Avatar">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm"
                                 style="width: 150px; height: 150px; font-size: 3rem;">
                                {{ strtoupper(substr($siswa->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h5 class="mb-1 fw-bold">{{ $siswa->name }}</h5>
                    <p class="text-muted mb-2">{{ $siswa->email }}</p>
                    <span class="badge bg-info px-3 py-2 rounded-pill">Siswa - Kelas {{ $siswa->kelas }}</span>

                    <hr class="my-4">

                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h4 class="mb-0 text-primary fw-bold">{{ $stats['total_materi_dibuka'] }}</h4>
                            <small class="text-muted">Materi Dibuka</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-success fw-bold">{{ number_format($stats['rata_rata_nilai'], 1) }}</h4>
                            <small class="text-muted">Rata-rata Nilai</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="col-lg-8">
            <!-- Update Profile Form -->
            <div class="card border-0 shadow-sm rounded-xl mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i> Edit Biodata
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('siswa.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $siswa->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $siswa->email) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">NISN</label>
                            <input type="text" 
                                class="form-control bg-light" 
                                name="nisn" 
                                value="{{ $siswa->nisn }}" 
                                readonly 
                                title="Hubungi Admin untuk mengubah data ini">
                                <small class="text-muted">
                                    <i class="bi bi-lock-fill"></i> 
                                    Data terkunci. Hanya Admin yang dapat mengubah.
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Kelas</label>
                                <select class="form-select" name="kelas" required>
                                    @foreach(config('lms.daftar_kelas', ['1A','1B','2A','2B','3A','3B','4A','4B','5A','5B','6A','6B']) as $kelas)
                                    <option value="{{ $kelas }}" {{ old('kelas', auth()->user()->kelas) == $kelas ? 'selected' : '' }}>
                                        Kelas {{ $kelas }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Foto Profil</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm rounded-xl">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-warning">
                        <i class="bi bi-key me-2"></i> Ganti Password
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('siswa.password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Password Baru</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning text-white fw-bold">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection