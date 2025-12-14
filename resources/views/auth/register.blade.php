<x-guest-layout>
    <div class="card card-login animate__animated animate__fadeInDown">
        <div class="login-header">
            <div class="mb-3">
                <img src="{{ asset('img/logo-sekolah.png') }}" alt="Logo Sekolah" style="width: 80px; height: 80px; background: white; border-radius: 50%; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            </div>
            <h4 class="fw-bold mb-0">Buat Akun Baru</h4>
            <p class="small text-white-50 mb-0">Lengkapi data diri Anda untuk mendaftar</p>
        </div>

        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Gagal Mendaftar!</strong> Periksa inputan Anda.
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" 
                           placeholder="Nama Lengkap" required autofocus>
                    <label for="name"><i class="bi bi-person me-1"></i> Nama Lengkap</label>
                    @error('name')
                        <div class="invalid-feedback ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="name@example.com" required>
                    <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
                    @error('email')
                        <div class="invalid-feedback ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           placeholder="Password" required autocomplete="new-password"
                           style="padding-right: 40px;">
                    <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                    
                    <span onclick="toggleRegPassword('password', 'iconPass1')" style="position: absolute; right: 15px; top: 18px; cursor: pointer; color: #6c757d; z-index: 10;">
                        <i class="bi bi-eye" id="iconPass1"></i>
                    </span>

                    @error('password')
                        <div class="invalid-feedback ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" 
                           placeholder="Konfirmasi Password" required autocomplete="new-password"
                           style="padding-right: 40px;">
                    <label for="password_confirmation"><i class="bi bi-check-circle me-1"></i> Ulangi Password</label>
                    
                    <span onclick="toggleRegPassword('password_confirmation', 'iconPass2')" style="position: absolute; right: 15px; top: 18px; cursor: pointer; color: #6c757d; z-index: 10;">
                        <i class="bi bi-eye" id="iconPass2"></i>
                    </span>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary-custom rounded-pill text-white shadow-sm">
                        <i class="bi bi-person-plus-fill me-2"></i> DAFTAR SEKARANG
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Sudah punya akun? 
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #4e73df;">
                            Login disini
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center mt-3 text-muted small">
        &copy; {{ date('Y') }} {{ config('app.name') }}.
    </div>

    <script>
        function toggleRegPassword(fieldId, iconId) {
            var field = document.getElementById(fieldId);
            var icon = document.getElementById(iconId);
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    </script>
</x-guest-layout>