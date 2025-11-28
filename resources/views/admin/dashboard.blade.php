@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Super Admin</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ auth()->user()->name }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-blue-500 p-3">
                            <i class="bi bi-people-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    <span class="text-green-600">{{ $stats['active_users'] }} Aktif</span> • 
                    <span class="text-red-600">{{ $stats['inactive_users'] }} Nonaktif</span>
                </div>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <i class="bi bi-person-badge-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Guru</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_guru'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Siswa -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-purple-500 p-3">
                            <i class="bi bi-person-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_siswa'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Materi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-orange-500 p-3">
                            <i class="bi bi-journal-text text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Materi</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_materi'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    {{ $stats['total_kuis'] }} Kuis
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Siswa per Kelas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Siswa per Kelas</h3>
                <div class="space-y-3">
                    @foreach($siswa_per_kelas as $kelas => $jumlah)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Kelas {{ $kelas }}</span>
                            <span class="font-semibold text-gray-900">{{ $jumlah }} siswa</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['total_siswa'] > 0 ? ($jumlah / $stats['total_siswa'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Absensi Chart -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Absensi</h3>
                <div class="space-y-3">
                    @foreach($absensi_chart['labels'] as $index => $label)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-semibold text-gray-900">{{ $absensi_chart['data'][$index] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $index == 0 ? 'bg-green-600' : ($index == 1 ? 'bg-red-600' : ($index == 2 ? 'bg-yellow-600' : 'bg-blue-600')) }}" 
                                 style="width: {{ $stats['total_absensi'] > 0 ? ($absensi_chart['data'][$index] / $stats['total_absensi'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">User Terbaru</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($recent_users as $user)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full {{ $user->role == 'super_admin' ? 'bg-red-100 text-red-800' : ($user->role == 'guru' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Materi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Materi Terbaru</h3>
                    <a href="{{ route('admin.materi.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($recent_materi as $materi)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $materi->judul }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="bi bi-person"></i> {{ $materi->guru->name }} • 
                                    <i class="bi bi-book"></i> Kelas {{ $materi->kelas }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $materi->tipe == 'kuis' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($materi->tipe) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.users.create') }}" class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-person-plus-fill text-2xl text-blue-600"></i>
                        <p class="mt-2 text-sm font-medium text-blue-900">Tambah User</p>
                    </div>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-people-fill text-2xl text-green-600"></i>
                        <p class="mt-2 text-sm font-medium text-green-900">Kelola User</p>
                    </div>
                </a>
                <a href="{{ route('admin.materi.index') }}" class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-journal-text text-2xl text-purple-600"></i>
                        <p class="mt-2 text-sm font-medium text-purple-900">Lihat Materi</p>
                    </div>
                </a>
                <a href="{{ route('admin.absensi.index') }}" class="flex items-center justify-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-check2-square text-2xl text-orange-600"></i>
                        <p class="mt-2 text-sm font-medium text-orange-900">Lihat Absensi</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection