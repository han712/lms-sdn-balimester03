@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Guru</h1>
        <p class="mt-1 text-sm text-gray-600">Selamat datang, {{ auth()->user()->name }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Materi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-blue-500 p-3">
                            <i class="bi bi-journal-text text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Materi</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_materi'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    <span class="text-green-600">{{ $stats['published_materi'] }} Published</span> • 
                    <span class="text-gray-600">{{ $stats['draft_materi'] }} Draft</span>
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
                        <p class="text-sm font-medium text-gray-500">Total Kuis</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_kuis'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Absensi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-500 p-3">
                            <i class="bi bi-check-circle-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Absensi</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_absensi'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kuis Belum Dinilai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-red-500 p-3">
                            <i class="bi bi-exclamation-circle-fill text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Belum Dinilai</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['jawaban_belum_dinilai'] }}</p>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    {{ $stats['total_jawaban'] }} Total Jawaban
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Lists Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Materi per Kelas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Materi per Kelas</h3>
                <div class="space-y-3">
                    @foreach($materi_per_kelas as $kelas => $jumlah)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Kelas {{ $kelas }}</span>
                            <span class="font-semibold text-gray-900">{{ $jumlah }} materi</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['total_materi'] > 0 ? ($jumlah / $stats['total_materi'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Kuis Perlu Dinilai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Kuis Perlu Dinilai</h3>
                    @if($kuis_pending->count() > 0)
                    <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $kuis_pending->count() }}</span>
                    @endif
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @forelse($kuis_pending as $jawaban)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $jawaban->siswa->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $jawaban->materi->judul }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $jawaban->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('guru.materi.jawaban-kuis', $jawaban->materi) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                Nilai →
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Tidak ada kuis yang perlu dinilai</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Materi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Materi Terbaru</h3>
                    <a href="{{ route('guru.materi.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($recent_materi as $materi)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $materi->judul }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Kelas {{ $materi->kelas }} • {{ $materi->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span class="text-xs px-2 py-1 rounded-full {{ $materi->tipe == 'kuis' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($materi->tipe) }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $materi->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $materi->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada materi</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Absensi -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Terbaru</h3>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @forelse($recent_absensi as $absensi)
                    <div class="py-2 border-b border-gray-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $absensi->siswa->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $absensi->materi->judul }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $absensi->waktu_akses ? $absensi->waktu_akses->diffForHumans() : '-' }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $absensi->status == 'hadir' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($absensi->status) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada absensi</p>
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
                <a href="{{ route('guru.materi.create') }}" class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-plus-circle-fill text-2xl text-blue-600"></i>
                        <p class="mt-2 text-sm font-medium text-blue-900">Upload Materi</p>
                    </div>
                </a>
                <a href="{{ route('guru.materi.index') }}" class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-list-ul text-2xl text-green-600"></i>
                        <p class="mt-2 text-sm font-medium text-green-900">Lihat Materi</p>
                    </div>
                </a>
                <a href="{{ route('guru.materi.index', ['tipe' => 'kuis']) }}" class="flex items-center justify-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-clipboard-check text-2xl text-yellow-600"></i>
                        <p class="mt-2 text-sm font-medium text-yellow-900">Kelola Kuis</p>
                    </div>
                </a>
                <button onclick="document.getElementById('exportModal').classList.remove('hidden')" class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                    <div class="text-center">
                        <i class="bi bi-download text-2xl text-purple-600"></i>
                        <p class="mt-2 text-sm font-medium text-purple-900">Export Absensi</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Export Absensi</h3>
            <form action="{{ route('guru.absensi.export') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="periode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="semester1">Semester 1 (Jul-Des)</option>
                            <option value="semester2">Semester 2 (Jan-Jun)</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <input type="number" name="tahun" value="{{ date('Y') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas (Opsional)</label>
                        <select name="kelas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua Kelas</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Kelas {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection