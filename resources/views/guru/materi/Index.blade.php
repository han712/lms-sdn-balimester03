<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Materi') }}
            </h2>
            <a href="{{ route('guru.materi.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                + Buat Materi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter Section --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('guru.materi.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        {{-- Search --}}
                        <div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Cari materi..." 
                                   class="w-full rounded-lg border-gray-300">
                        </div>

                        {{-- Filter Kelas --}}
                        <div>
                            <select name="kelas" class="w-full rounded-lg border-gray-300">
                                <option value="">Semua Kelas</option>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ request('kelas') == $i ? 'selected' : '' }}>
                                        Kelas {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filter Tipe --}}
                        <div>
                            <select name="tipe" class="w-full rounded-lg border-gray-300">
                                <option value="">Semua Tipe</option>
                                <option value="materi" {{ request('tipe') == 'materi' ? 'selected' : '' }}>Materi</option>
                                <option value="kuis" {{ request('tipe') == 'kuis' ? 'selected' : '' }}>Kuis</option>
                            </select>
                        </div>

                        {{-- Filter Status --}}
                        <div>
                            <select name="status" class="w-full rounded-lg border-gray-300">
                                <option value="">Semua Status</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                🔍 Filter
                            </button>
                            <a href="{{ route('guru.materi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Materi List --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    
                    @if($materi->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($materi as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $item->judul }}</div>
                                                        <div class="text-sm text-gray-500">{{ Str::limit($item->deskripsi, 50) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Kelas {{ $item->kelas }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->tipe === 'kuis' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst($item->tipe) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="{{ route('guru.materi.toggle-publish', $item) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $item->is_published ? '✓ Published' : '○ Draft' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->created_at->diffForHumans() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('guru.materi.show', $item) }}" class="text-blue-600 hover:text-blue-900">
                                                        👁️
                                                    </a>
                                                    <a href="{{ route('guru.materi.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        ✏️
                                                    </a>
                                                    
                                                    @if($item->tipe === 'materi')
                                                        <a href="{{ route('guru.materi.absensi', $item) }}" class="text-green-600 hover:text-green-900">
                                                            📋
                                                        </a>
                                                    @else
                                                        <a href="{{ route('guru.materi.jawaban-kuis', $item) }}" class="text-purple-600 hover:text-purple-900">
                                                            📝
                                                        </a>
                                                    @endif
                                                    
                                                    <form action="{{ route('guru.materi.destroy', $item) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Yakin ingin menghapus materi ini?')"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $materi->links() }}
                        </div>

                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada materi</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat materi baru</p>
                            <div class="mt-6">
                                <a href="{{ route('guru.materi.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    + Buat Materi Baru
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>