<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LMS SD') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }

        /* Navbar Styling */
        .navbar-lms {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }

        /* Utilities Sudut Membulat */
        .rounded-xl { border-radius: 1rem !important; }
        .rounded-2xl { border-radius: 1.5rem !important; }
        
        /* Efek Hover Kartu */
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        
        /* Warna Gradasi Modern */
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
        .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
        .bg-gradient-danger { background: linear-gradient(45deg, #e74a3b, #be2617); }

        /* Helpers */
        .text-gray-800 { color: #2d3748 !important; }
        .rotate-n-15 { transform: rotate(-15deg); }
        .z-index-1 { z-index: 1; }
        a { text-decoration: none; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-lms sticky-top mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="{{ route('siswa.dashboard') }}">
                <i class="bi bi-mortarboard-fill fs-3 me-2"></i>
                LMS SD
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-3">
                    @if(auth()->check() && auth()->user()->role === 'siswa')
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('siswa.dashboard') ? 'text-primary active' : 'text-secondary' }}" href="{{ route('siswa.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('siswa.materi.*') ? 'text-primary active' : 'text-secondary' }}" href="{{ route('siswa.materi.index') }}">
                            <i class="bi bi-book me-1"></i> Materi & Kuis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold {{ request()->routeIs('siswa.riwayat-absensi') ? 'text-primary active' : 'text-secondary' }}" href="{{ route('siswa.riwayat-absensi') }}">
                            <i class="bi bi-clock-history me-1"></i> Absensi
                        </a>
                    </li>
                    @endif
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="d-none d-md-block text-start">
                                <div class="fw-bold small">{{ Auth::user()->name }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Kelas {{ Auth::user()->kelas }}</div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-xl">
                            <li><a class="dropdown-item py-2" href="{{ route('siswa.profile.edit') }}"><i class="bi bi-person-gear me-2"></i> Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="container-fluid px-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-xl mb-4 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-xl mb-4 d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>