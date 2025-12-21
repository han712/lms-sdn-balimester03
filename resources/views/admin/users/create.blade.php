@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tambah User Baru</h6>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <h6 class="heading-small text-muted mb-4">Informasi Akun (Login)</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Nama lengkap pengguna">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="email@sekolah.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Minimal 8 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi password">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Peran / Role <span class="text-danger">*</span></label>
                            <select name="role" id="roleSelector" class="form-select @error('role') is-invalid @enderror" onchange="toggleForm()" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin / Staff</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck" value="1" checked>
                            <label class="form-check-label" for="activeCheck">Aktifkan Akun User Ini</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div id="formSiswa" style="display: none;">
                    <h6 class="heading-small text-primary mb-4"><i class="fas fa-user-graduate me-1"></i> Data Akademik & Pribadi Siswa</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">NISN <span class="text-danger">*</span></label>
                                <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="Nomor Induk Siswa Nasional">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">NIS (Opsional)</label>
                                <input type="text" name="nis" class="form-control" value="{{ old('nis') }}" placeholder="Nomor Induk Sekolah">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas" class="form-select">
                                    <option value="">Pilih</option>
                                    @foreach(range(1, 6) as $k)
                                        <option value="{{ $k }}" {{ old('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label">Angkatan</label>
                                <input type="number" name="tahun_masuk" class="form-control" value="{{ old('tahun_masuk', date('Y')) }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select">
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted mt-3 mb-3 small font-weight-bold text-uppercase">Data Orang Tua</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Ibu Kandung</label>
                                <input type="text" name="nama_ibu" class="form-control" value="{{ old('nama_ibu') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Ayah</label>
                                <input type="text" name="nama_ayah" class="form-control" value="{{ old('nama_ayah') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">No. HP Orang Tua</label>
                                <input type="text" name="no_hp_ortu" class="form-control" value="{{ old('no_hp_ortu') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Pekerjaan Orang Tua</label>
                                <input type="text" name="pekerjaan_ortu" class="form-control" value="{{ old('pekerjaan_ortu') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="formGuru" style="display: none;">
                    <h6 class="heading-small text-success mb-4"><i class="fas fa-chalkboard-teacher me-1"></i> Data Kepegawaian Guru</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">NIP / NUPTK <span class="text-danger">*</span></label>
                                <input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Status Kepegawaian</label>
                                <select name="status_kepegawaian" class="form-select">
                                    <option value="PNS" {{ old('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS</option>
                                    <option value="GTY" {{ old('status_kepegawaian') == 'GTY' ? 'selected' : '' }}>Guru Tetap Yayasan</option>
                                    <option value="GTT" {{ old('status_kepegawaian') == 'GTT' ? 'selected' : '' }}>Honorer / GTT</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Pendidikan Terakhir</label>
                                <select name="pendidikan_terakhir" class="form-select">
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="D3">D3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Mata Pelajaran Utama</label>
                                <input type="text" name="mata_pelajaran_utama" class="form-control" placeholder="Cth: Matematika" value="{{ old('mata_pelajaran_utama') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">Tugas Wali Kelas (Opsional)</label>
                                <select name="wali_kelas" class="form-select">
                                    <option value="">Bukan Wali Kelas</option>
                                    @foreach(range(1, 6) as $k)
                                        <option value="{{ $k }}" {{ old('wali_kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Jabatan Tambahan</label>
                                <input type="text" name="jabatan_tambahan" class="form-control" placeholder="Cth: Kepala Perpustakaan" value="{{ old('jabatan_tambahan') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="formAdmin" style="display: none;">
                    <h6 class="heading-small text-danger mb-4"><i class="fas fa-user-cog me-1"></i> Profil Admin / Staff</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">ID Pegawai / Staff</label>
                                <input type="text" name="id_pegawai" class="form-control" value="{{ old('id_pegawai') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Posisi / Bagian</label>
                                <input type="text" name="posisi" class="form-control" placeholder="Cth: Tata Usaha" value="{{ old('posisi') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mt-5">
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-5 shadow">
                        <i class="fas fa-save me-1"></i> SIMPAN USER BARU
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleForm() {
        const role = document.getElementById('roleSelector').value;
        const formSiswa = document.getElementById('formSiswa');
        const formGuru = document.getElementById('formGuru');
        const formAdmin = document.getElementById('formAdmin');

        if(formSiswa) formSiswa.style.display = 'none';
        if(formGuru) formGuru.style.display = 'none';
        if(formAdmin) formAdmin.style.display = 'none';

        if (role === 'siswa' && formSiswa) formSiswa.style.display = 'block';
        else if (role === 'guru' && formGuru) formGuru.style.display = 'block';
        else if (role === 'admin' && formAdmin) formAdmin.style.display = 'block';
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleForm();
    });
</script>
@endpush
@endsection