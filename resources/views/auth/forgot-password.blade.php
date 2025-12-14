<x-guest-layout>
    <div class="card card-login animate__animated animate__fadeInDown">
        <div class="login-header">
            <div class="mb-3">
                <img src="{{ asset('img/logo-sekolah.png') }}" alt="Logo Sekolah" style="width: 80px; height: 80px; background: white; border-radius: 50%; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            </div>
            <h4 class="fw-bold mb-0">Lupa Password?</h4>
            <p class="small text-white-50 mb-0">Reset password akun Anda dengan mudah</p>
        </div>

        <div class="card-body p-4">
            <div class="mb-4 text-muted small text-center">
                {{ __('Lupa password Anda? Tidak masalah. Masukkan alamat email Anda yang terdaftar, dan kami akan mengirimkan tautan untuk mereset password.') }}
            </div>

            <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>Email tidak ditemukan</strong> atau format salah.
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-floating mb-4">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="name@example.com" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
                    @error('email')
                        <div class="invalid-feedback ps-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-custom rounded-pill text-white shadow-sm">
                        <i class="bi bi-send me-2"></i> KIRIM LINK RESET
                    </button>
                    
                    <a href="{{ route('login') }}" class="btn btn-light rounded-pill border shadow-sm text-secondary">
                        <i class="bi bi-arrow-left me-2"></i> KEMBALI KE LOGIN
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center mt-3 text-muted small">
        &copy; {{ date('Y') }} {{ config('app.name') }}.
    </div>
</x-guest-layout>