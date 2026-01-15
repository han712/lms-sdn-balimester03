@extends('layouts.guru')

@section('title', 'Kelola Absensi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-check"></i> Kelola Absensi
        </h1>
        <a href="{{ route('guru.materi.show', $materi->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Materi
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $materi->judul }} (Kelas {{ $materi->kelas }})
            </h6>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('guru.materi.absensi.update', $materi->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm btn-success mr-2" onclick="setAll('hadir')">
                        <i class="fas fa-check-double"></i> Set Semua Hadir
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 50px">No</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th style="width: 420px">Status Kehadiran</th>
                                <th>Waktu Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswaList as $index => $siswa)
                                @php
                                    $row = $absensiData->get($siswa->id);
                                    $status = $row->status ?? 'tidak_hadir'; // ✅ default sesuai enum
                                    $waktu = $row->waktu_akses ?? null;
                                @endphp

                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $siswa->name }}</td>
                                    <td>{{ $siswa->nisn }}</td>
                                    <td>
                                        <div class="d-flex justify-content-between px-3 flex-wrap">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="h_{{ $siswa->id }}" name="absensi[{{ $siswa->id }}]"
                                                       class="custom-control-input radio-hadir" value="hadir"
                                                       {{ $status === 'hadir' ? 'checked' : '' }}>
                                                <label class="custom-control-label text-success" for="h_{{ $siswa->id }}">Hadir</label>
                                            </div>

                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="i_{{ $siswa->id }}" name="absensi[{{ $siswa->id }}]"
                                                       class="custom-control-input radio-izin" value="izin"
                                                       {{ $status === 'izin' ? 'checked' : '' }}>
                                                <label class="custom-control-label text-info" for="i_{{ $siswa->id }}">Izin</label>
                                            </div>

                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="s_{{ $siswa->id }}" name="absensi[{{ $siswa->id }}]"
                                                       class="custom-control-input radio-sakit" value="sakit"
                                                       {{ $status === 'sakit' ? 'checked' : '' }}>
                                                <label class="custom-control-label text-warning" for="s_{{ $siswa->id }}">Sakit</label>
                                            </div>

                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="t_{{ $siswa->id }}" name="absensi[{{ $siswa->id }}]"
                                                       class="custom-control-input radio-tidak_hadir" value="tidak_hadir"
                                                       {{ $status === 'tidak_hadir' ? 'checked' : '' }}>
                                                <label class="custom-control-label text-danger" for="t_{{ $siswa->id }}">Tidak Hadir</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($waktu)
                                            <small>{{ \Carbon\Carbon::parse($waktu)->format('H:i d/m/y') }}</small>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada siswa di kelas ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Simpan Data Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function setAll(status) {
    const radios = document.querySelectorAll('.radio-' + status);
    radios.forEach(r => r.checked = true);
}
</script>
@endpush
@endsection
