<x-app-layout>

    <div class="container">

        <h2 class="mb-4">Data Siswa</h2>

        {{-- SEARCH FORM --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="" id="filterForm">
                    <div class="row">

                        {{-- Search Nama --}}
                        <div class="col-md-4">
                            <label>Search Nama Siswa</label>
                            <input 
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Cari nama siswa..."
                                oninput="document.getElementById('filterForm').submit()"
                            >
                        </div>

                        {{-- Filter Kelas --}}
                        <div class="col-md-3">
                            <label>Kelas</label>
                            <input 
                                type="text"
                                name="kelas"
                                value="{{ request('kelas') }}"
                                class="form-control"
                                placeholder="Misal: 1A"
                                oninput="document.getElementById('filterForm').submit()"
                            >
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="card">
            <div class="card-body p-0">

                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>L/P</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>
                    <tbody>

                        @php
                            $filtered = collect($data)
                                ->filter(function ($s) {
                                    return !empty($s['nama'])       // nama tidak boleh kosong
                                        && $s['nama'] !== '-'       // nama tidak boleh "-"
                                        && in_array($s['gender'], ['L','P']); // gender harus valid
                                })
                                ->values();
                            @endphp

                        @foreach ($filtered as $siswa)
                            <tr>
                                <td>{{ $siswa['no'] }}</td>
                                <td>{{ $siswa['nama'] }}</td>
                                <td>{{ $siswa['gender'] }}</td>
                                <td>{{ $siswa['kelas'] }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</x-app-layout>