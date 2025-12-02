<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Siswa') - LMS SD</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            padding-top: 1rem;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .sidebar::-webkit-scrollbar {
            width: 0.35rem;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 0.35rem;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 4.375rem;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 800;
            padding: 0 1rem;
            color: white;
        }

        .sidebar-brand i {
            font-size: 2rem;
            margin-right: 0.5rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 1rem 0;
        }

        .sidebar-heading {
            font-size: 0.65rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.4);
            padding: 0.5rem 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 3px solid white;
        }

        .nav-link i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            width: 1.5rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
        }

        /* Top Navigation */
        .topbar {
            height: 4.375rem;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0 1.5rem;
        }

        .topbar .navbar-nav .nav-item .nav-link {
            height: 4.375rem;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            color: #858796;
        }

        .topbar .navbar-nav .nav-item .nav-link:hover {
            color: #4e73df;
        }

        .user-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Content Area */
        .content-area {
            padding: 1.5rem;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            color: #4e73df;
        }

        /* Stat Cards */
        .stat-card {
            border-left: 4px solid;
            padding: 1.25rem;
            height: 100%;
        }

        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        /* Alerts */
        .alert {
            border-radius: 0.35rem;
            border: none;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 0.35rem;
        }

        /* Buttons */
        .btn {
            border-radius: 0.35rem;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
                transition: margin-left 0.3s;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <a class="sidebar-brand" href="{{ route('siswa.materi.index') }}">
                <i class="bi bi-book-fill"></i>
                <span>LMS Siswa SD</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Menu Utama</div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.materi.index') ? 'active' : '' }}" 
                       href="{{ route('siswa.materi.index') }}">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.materi.show') ? 'active' : '' }}" 
                       href="{{ route('siswa.materi.index') }}">
                        <i class="bi bi-journal-text"></i>
                        <span>Materi Pelajaran</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Riwayat</div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.riwayat-kuis') ? 'active' : '' }}" 
                       href="{{ route('siswa.riwayat-kuis') }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Riwayat Kuis</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.riwayat-absensi') ? 'active' : '' }}" 
                       href="{{ route('siswa.riwayat-absensi') }}">
                        <i class="bi bi-calendar-check"></i>
                        <span>Riwayat Absensi</span>
                    </a>
                </li>
            </ul>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Akun</div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('siswa.profile.edit') ? 'active' : '' }}" 
                       href="{{ route('siswa.profile.edit') }}">
                        <i class="bi bi-person-circle"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="nav-link" href="#" 
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Keluar</span>
                        </a>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <nav class="navbar topbar navbar-expand navbar-light bg-white mb-4">
                <button class="btn btn-link d-md-none mobile-toggle" id="sidebarToggle">
                    <i class="bi bi-list fs-3"></i>
                </button>

                <ul class="navbar-nav ms-auto">
                    <!-- User Info -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                     class="user-avatar me-2" alt="Avatar">
                            @else
                                <i class="bi bi-person-circle fs-4 me-2"></i>
                            @endif
                            <span class="d-none d-lg-inline text-gray-600 small">
                                {{ auth()->user()->name }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ route('siswa.profile.edit') }}">
                                    <i class="bi bi-person me-2"></i>Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="#"
                                       onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Page Content -->
            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>