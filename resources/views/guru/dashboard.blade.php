<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                
                {{-- Total Materi --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Materi</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_materi'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Published Materi --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Published</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['published_materi'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Kuis --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Kuis</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_kuis'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Belum Dinilai --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Perlu Dinilai</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['jawaban_belum_dinilai'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left Column --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Grafik Materi per Kelas --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Materi per Kelas</h3>
                            <div class="space-y-3">
                                @foreach(['1', '2', '3', '4', '5', '6'] as $kelas)
                                    @php
                                $total = $materi_per_kelas[$kelas] ?? 0;
                                
                                
                                if (!empty($materi_per_kelas)) {
                                    $maxMateri = max($materi_per_kelas);
                                } else {
                                    $maxMateri = 1; // Default kalau belum ada data sama sekali
                                }
                                
                                $percentage = ($total / $maxMateri) * 100;
                                @endphp
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">Kelas {{ $kelas }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $total }} materi</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Recent Materi --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Materi Terbaru</h3>
                                <a href="{{ route('guru.materi.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                            </div>
                            <div class="space-y-3">
                                @forelse($recent_materi as $materi)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $materi->judul }}</p>
                                            <p class="text-sm text-gray-500">
                                                Kelas {{ $materi->kelas }} • 
                                                <span class="capitalize">{{ $materi->tipe }}</span>
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $materi->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $materi->is_published ? 'Published' : 'Draft' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">Belum ada materi</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column --}}
                <div class="space-y-6">
                    
                    {{-- Quick Actions --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-2">
                                <a href="{{ route('guru.materi.create') }}" class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700">
                                    + Buat Materi Baru
                                </a>
                                <a href="{{ route('guru.materi.index', ['tipe' => 'kuis']) }}" class="block w-full px-4 py-2 bg-purple-600 text-white text-center rounded-lg hover:bg-purple-700">
                                    📝 Kelola Kuis
                                </a>
                                <a href="{{ route('guru.materi.index') }}" class="block w-full px-4 py-2 bg-gray-600 text-white text-center rounded-lg hover:bg-gray-700">
                                    📚 Lihat Semua Materi
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Kuis Pending Review --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Perlu Dinilai</h3>
                                @if($stats['jawaban_belum_dinilai'] > 0)
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                        {{ $stats['jawaban_belum_dinilai'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="space-y-3">
                                @forelse($kuis_pending as $jawaban)
                                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="font-medium text-gray-900">{{ $jawaban->siswa->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $jawaban->materi->judul }}</p>
                                        <a href="{{ route('guru.materi.jawaban-kuis', $jawaban->materi_id) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                            Nilai Sekarang →
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">Tidak ada kuis yang perlu dinilai</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Recent Absensi --}}
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Terbaru</h3>
                            <div class="space-y-3">
                                @forelse($recent_absensi->take(5) as $absen)
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $absen->siswa->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $absen->materi->judul }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $absen->status === 'hadir' ? 'bg-green-100 text-green-800' : 
                                            ($absen->status === 'izin' ? 'bg-blue-100 text-blue-800' : 
                                            ($absen->status === 'sakit' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'))
                                        }}">
                                            {{ ucfirst($absen->status) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">Belum ada absensi</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>