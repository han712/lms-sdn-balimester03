@extends('layouts.app')

@section('content')
<div class="py-8 px-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold flex items-center gap-2 mb-6">
        <i class="bi bi-people-fill text-blue-600"></i> Data Guru
    </h1>

    @if($guruList->isEmpty())
        <div class="text-center py-10 text-gray-500">
            <i class="bi bi-info-circle text-2xl mb-2"></i><br>
            Tidak ada data guru yang tersedia.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($guruList as $guru)
                @php
                    // Tentukan warna border berdasarkan mapel
                    $mapel = strtolower($guru->mapel ?? '');
                    $borderColor = match(true) {
                        str_contains($mapel, 'agama') => 'border-purple-400',
                        str_contains($mapel, 'matematika') => 'border-blue-400',
                        str_contains($mapel, 'penjaskes') || str_contains($mapel, 'olahraga') => 'border-green-400',
                        str_contains($mapel, 'ipa') => 'border-teal-400',
                        str_contains($mapel, 'ips') => 'border-yellow-400',
                        str_contains($mapel, 'bahasa') => 'border-pink-400',
                        default => 'border-gray-300',
                    };
                @endphp

                <!-- Card Guru -->
                <div class="bg-white border-2 {{ $borderColor }} rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col">

                    <!-- Foto (Tinggi seragam dan sejajar antar baris) -->
                    <!-- Foto (rata sejajar semua pakai CSS custom) -->
                <div class="guru-photo-wrapper">
                    <img 
                        src="{{ $guru->foto_url }}" 
                        alt="{{ $guru->name }}" 
                        class="guru-photo"
                        loading="lazy"
                    >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    </div>

                    <!-- Info Guru -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h2 class="font-semibold text-lg text-gray-800 mb-1 group-hover:text-blue-700 transition">
                                {{ $guru->name }}
                            </h2>
                            <div class="text-sm text-gray-600 space-y-1 leading-relaxed">
                                <p><span class="font-medium">Tempat Lahir:</span> {{ $guru->tempat_lahir ?? '-' }}</p>
                                <p><span class="font-medium">Tanggal Lahir:</span> {{ $guru->tanggal_lahir ?? '-' }}</p>
                                <p><span class="font-medium">Agama:</span> {{ $guru->agama ?? '-' }}</p>
                                <p><span class="font-medium">NIP:</span> {{ $guru->nip ?? '-' }}</p>
                                <p><span class="font-medium">Guru:</span> {{ $guru->mapel ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
