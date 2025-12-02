<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="text-xl font-bold text-gray-800">
                        <i class="bi bi-book-fill text-blue-600"></i> LMS SD
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user()->role === 'super_admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            <i class="bi bi-people"></i> Kelola User
                        </x-nav-link>
                        <x-nav-link :href="route('admin.materi.index')" :active="request()->routeIs('admin.materi.*')">
                            <i class="bi bi-journal-text"></i> Semua Materi
                        </x-nav-link>
                        <x-nav-link :href="route('admin.absensi.index')" :active="request()->routeIs('admin.absensi.*')">
                            <i class="bi bi-check2-square"></i> Semua Absensi
                        </x-nav-link>
                    @elseif(auth()->user()->role === 'guru')
                        <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('guru.data-siswa')" :active="request()->routeIs('guru.data-siswa')">
                            <i class="bi bi-person-badge"></i> Data Siswa
                        </x-nav-link>
                        <x-nav-link :href="route('guru.materi.index')" :active="request()->routeIs('guru.materi.*')">
                            <i class="bi bi-journal-text"></i> Materi & Kuis
                        </x-nav-link>
                        <x-nav-link :href="route('guru.data-guru')" :active="request()->routeIs('guru.data-guru')">
                            <i class="bi bi-person-workspace"></i> Data Guru
                        </x-nav-link>                     
                    @elseif(auth()->user()->role === 'siswa')
                        <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('siswa.materi.index')" :active="request()->routeIs('siswa.materi.*')">
                            <i class="bi bi-book"></i> Materi & Kuis
                        </x-nav-link>
                        <x-nav-link :href="route('siswa.riwayat-absensi')" :active="request()->routeIs('siswa.riwayat-absensi')">
                            <i class="bi bi-clock-history"></i> Riwayat Absensi
                        </x-nav-link>
                        <x-nav-link :href="route('siswa.riwayat-kuis')" :active="request()->routeIs('siswa.riwayat-kuis')">
                            <i class="bi bi-file-text"></i> Riwayat Kuis
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold mr-2">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                                </div>
                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="bi bi-person"></i> Profile
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->role === 'super_admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    Kelola User
                </x-responsive-nav-link>
            @elseif(auth()->user()->role === 'guru')
                <x-responsive-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')">
                    Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('guru.materi.index')" :active="request()->routeIs('guru.materi.*')">
                    Materi & Kuis
                </x-responsive-nav-link>
            @elseif(auth()->user()->role === 'siswa')
                <x-responsive-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')">
                    Dashboard
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('siswa.materi.index')" :active="request()->routeIs('siswa.materi.*')">
                    Materi & Kuis
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ auth()->user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Logout
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>