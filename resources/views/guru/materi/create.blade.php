<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Materi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    
                    <form action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Judul --}}
                        <div class="mb-6">
                            <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Materi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="judul" 
                                   id="judul"
                                   value="{{ old('judul') }}"
                                   class="w-full rounded-lg border-gray-300 @error('judul') border-red-500 @enderror"
                                   required>
                            @error('judul')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-6">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi <span class="text-red-500">*</span>
                            </label>
                            <textarea name="deskripsi" 
                                      id="deskripsi" 
                                      rows="5"
                                      class="w-full rounded-lg border-gray-300 @error('deskripsi') border-red-500 @enderror"
                                      required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Row: Tipe & Kelas --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            {{-- Tipe --}}
                            <div>
                                <label for="tipe" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipe <span class="text-red-500">*</span>
                                </label>
                                <select name="tipe" 
                                        id="tipe"
                                        class="w-full rounded-lg border-gray-300 @error('tipe') border-red-500 @enderror"
                                        required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="materi" {{ old('tipe') === 'materi' ? 'selected' : '' }}>📚 Materi</option>
                                    <option value="kuis" {{ old('tipe') === 'kuis' ? 'selected' : '' }}>📝 Kuis</option>
                                </select>
                                @error('tipe')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kelas --}}
                            <div>
                                <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kelas <span class="text-red-500">*</span>
                                </label>
                                <select name="kelas" 
                                        id="kelas"
                                        class="w-full rounded-lg border-gray-300 @error('kelas') border-red-500 @enderror"
                                        required>
                                    <option value="">Pilih Kelas</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('kelas') == $i ? 'selected' : '' }}>
                                            Kelas {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('kelas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- File Upload --}}
                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload File (Opsional)
                            </label>
                            <input type="file" 
                                   name="file" 
                                   id="file"
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                                   class="w-full border border-gray-300 rounded-lg p-2 @error('file') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">
                                Format: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG (Max: 50MB)
                            </p>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Publish Checkbox --}}
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_published" 
                                       value="1"
                                       {{ old('is_published') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Publish materi sekarang</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">
                                Jika tidak dicentang, materi akan disimpan sebagai draft
                            </p>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('guru.materi.index') }}" 
                               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                💾 Simpan Materi
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    {{-- Preview File (Optional Enhancement) --}}
    @push('scripts')
    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                console.log('File selected:', file.name, fileSize + ' MB');
            }
        });
    </script>
    @endpush

</x-app-layout>