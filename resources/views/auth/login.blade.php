<x-guest-layout>
    <div class="card card-login animate__animated animate__fadeInDown">
        <div class="login-header">
            <div class="mb-3">
                <img src="{{ asset('img/logo-sekolah.png') }}" alt="Logo Sekolah" style="width: 80px; height: 80px; background: white; border-radius: 50%; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            </div>
            <h4 class="fw-bold mb-0">Selamat Datang</h4>
            <p class="small text-white-50 mb-0">Silahkan login ke akun LMS Anda</p>
        </div>

        <div class="card-body p-4 pt-5">
            <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Whoops!</strong> Ada kesalahan data.
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="name@example.com" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
                    @error('email')
                        <div class="invalid-feedback text-start ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           placeholder="Password" required autocomplete="current-password"
                           style="padding-right: 40px;">
                    <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                    
                    <span onclick="togglePassword()" style="position: absolute; right: 15px; top: 18px; cursor: pointer; color: #6c757d; z-index: 10;">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </span>

                    @error('password')
                        <div class="invalid-feedback text-start ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label small text-muted" for="remember_me">
                            Ingat Saya
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a class="small text-decoration-none fw-bold" href="{{ route('password.request') }}" style="color: #4e73df;">
                            Lupa Password?
                        </a>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-custom rounded-pill text-white shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i> MASUK SEKARANG
                    </button>
                </div>

                <div class="text-center mt-4">
                    <p class="small text-muted">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Daftar disini</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-3 text-muted small">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</x-guest-layout>