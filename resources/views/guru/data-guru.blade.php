<x-app-layout> 
    <div class="py-8 px-6 max-w-7xl mx-auto">

        <h1 class="text-3xl font-bold flex items-center gap-2 mb-8">
            <i class="bi bi-people-fill text-blue-600"></i> Data Guru
        </h1>

        @if($guruList->isEmpty())
            <div class="text-center py-10 text-gray-500">
                <i class="bi bi-info-circle text-2xl mb-2"></i><br>
                Tidak ada data guru.
            </div>
        @else

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

            @foreach($guruList as $guru)
            <div class="bg-white border rounded-xl shadow-md overflow-hidden">

                <!-- FRAME FOTO RAPI, TIDAK CROP, TIDAK GEDE -->
                <div class="w-full bg-gray-100 overflow-hidden"
                    style="height: 300px;">
                    <img src="{{ $guru->foto_url }}"
                        alt="{{ $guru->name }}"
                        class="w-full h-full object-cover object-top">
                        style="height: {{ $guru->name == 'Lenny Fawatta' ? '340px' : '300px' }};">
                </div>

                <div class="p-4">
                    <h2 class="font-semibold text-lg text-gray-900 mb-2">
                        {{ $guru->name }}
                    </h2>

                    <div class="text-sm text-gray-700 space-y-1">
                        <p><strong>Tempat Lahir:</strong> {{ $guru->tempat_lahir ?? '-' }}</p>
                        <p><strong>Tanggal Lahir:</strong> {{ $guru->tanggal_lahir ?? '-' }}</p>
                        <p><strong>Agama:</strong> {{ $guru->agama ?? '-' }}</p>
                        <p><strong>NIP:</strong> {{ $guru->nip ?? '-' }}</p>
                        <p><strong>Guru:</strong> {{ $guru->mapel ?? '-' }}</p>
                    </div>
                </div>

            </div>
            @endforeach

        </div>

        @endif

    </div>
</x-app-layout>