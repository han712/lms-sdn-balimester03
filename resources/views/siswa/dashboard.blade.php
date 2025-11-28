@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Siswa</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ auth()->user()->name }} - Kelas {{ auth()->user()->kelas }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Materi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-blue-500 p-3">
                            <i class="bi bi-book-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Materi Tersedia</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_materi'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    {{ $stats['materi_diakses'] }} Diakses
                </div>
            </div>
        </div>

        <!-- Total Kuis -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-yellow-500 p-3">
                            <i class="bi bi-question-circle-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Kuis Tersedia</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_kuis'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    {{ $stats['kuis_dijawab'] }} Dijawab
                </div>
            </div>
        </div>

        <!-- Kuis Dinilai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <i class="bi bi-check-circle-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Kuis Dinilai</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['kuis_dinilai'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Nilai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-purple-500 p-3">
                            <i class="bi bi-trophy-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Rata-rata Nilai</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $stats['rata_nilai'] ? number_format($stats['rata_nilai'], 1) : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Materi Tersedia -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Materi & Kuis Terbaru</h2>
            <a href="{{ route('siswa.materi.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($available_materi as $materi)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs px-2 py-1 rounded-full {{ $materi->tipe == 'kuis' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($materi->tipe) }}
                        </span>
                        @if($materi->tanggal_selesai && \Carbon\Carbon::now()->diffInDays($materi->tanggal_selesai, false) <= 3 && \Carbon\Carbon::now()->diffInDays($materi->tanggal_selesai, false) >= 0)
                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                            <i class="bi bi-exclamation-circle"></i> {{ \Carbon\Carbon::now()->diffInDays($materi->tanggal_selesai) }} hari lagi
                        </span>
                        @endif
                    </div>
                    
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $materi->judul }}</h3>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $materi->keterangan ?? 'Tidak ada deskripsi' }}</p>
                    
                    <div class="flex items-center text-xs text-gray-500 mb-4">
                        <i class="bi bi-person mr-1"></i>
                        <span>{{ $materi->guru->name }}</span>
                        <span class="mx-2">•</span>
                        <i class="bi bi-calendar mr-1"></i>
                        <span>{{ $materi->tanggal_mulai->format('d M Y') }}</span>
                    </div>
                    
                    <a href="{{ route('siswa.materi.show', $materi) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Buka {{ ucfirst($materi->tipe) }}
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-center text-gray-500">
                    <i class="bi bi-inbox text-4xl mb-2"></i>
                    <p>Belum ada materi atau kuis yang tersedia</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Kuis Belum Dikerjakan -->
    @if($kuis_pending->count() > 0)
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800">Kuis yang Belum Dikerjakan</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($kuis_pending as $kuis)
                        <li>
                            <a href="{{ route('siswa.materi.show', $kuis) }}" class="underline hover:text-yellow-900">
                                {{ $kuis->judul }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Absensi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi Terakhir</h3>
                    <a href="{{ route('siswa.riwayat-absensi') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($recent_absensi as $absensi)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $absensi->materi->judul }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $absensi->waktu_akses ? $absensi->waktu_akses->format('d M Y, H:i') : '-' }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $absensi->status == 'hadir' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($absensi->status) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada riwayat absensi</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Nilai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Nilai Kuis Terakhir</h3>
                    <a href="{{ route('siswa.riwayat-kuis') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($recent_nilai as $nilai)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $nilai->materi->judul }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $nilai->dinilai_pada ? $nilai->dinilai_pada->format('d M Y') : '-' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $nilai->nilai >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $nilai->nilai }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $nilai->nilai_huruf }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada nilai kuis</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('siswa.materi.index') }}" class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-book text-2xl text-blue-600"></i>
                        <p class="mt-2 text-sm font-medium text-blue-900">Lihat Materi</p>
                    </div>
                </a>
                <a href="{{ route('siswa.materi.index', ['tipe' => 'kuis']) }}" class="flex items-center justify-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-question-circle text-2xl text-yellow-600"></i>
                        <p class="mt-2 text-sm font-medium text-yellow-900">Kuis</p>
                    </div>
                </a>
                <a href="{{ route('siswa.riwayat-absensi') }}" class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-clock-history text-2xl text-green-600"></i>
                        <p class="mt-2 text-sm font-medium text-green-900">Riwayat Absensi</p>
                    </div>
                </a>
                <a href="{{ route('siswa.riwayat-kuis') }}" class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-file-text text-2xl text-purple-600"></i>
                        <p class="mt-2 text-sm font-medium text-purple-900">Riwayat Kuis</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection