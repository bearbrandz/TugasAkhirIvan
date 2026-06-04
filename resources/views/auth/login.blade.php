@extends('layouts.app')

@section('content')
<style>
    body {
        background: #0f172a !important;
        color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    .auth-page {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
        background:
            radial-gradient(circle at top left, rgba(239, 68, 68, 0.22), transparent 30%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.16), transparent 28%),
            #0f172a;
    }

    .auth-card {
        width: 100%;
        max-width: 460px;
        background: #111827;
        border: 1px solid #26354d;
        border-radius: 22px;
        box-shadow: 0 22px 70px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }

    .auth-header {
        padding: 32px 32px 18px;
        text-align: center;
    }

    .auth-logo {
        width: 68px;
        height: 68px;
        border-radius: 18px;
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ef233c, #d90429);
        box-shadow: 0 12px 28px rgba(239, 35, 60, 0.35);
        color: #fff;
        font-size: 28px;
    }

    .auth-header h1 {
        margin: 0;
        color: #fff;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .auth-header p {
        margin: 8px 0 0;
        color: #94a3b8;
        font-size: 14px;
    }

    .auth-body {
        padding: 10px 32px 34px;
    }

    .form-label {
        color: #e5e7eb;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-control {
        background: #0b1220 !important;
        border: 1px solid #334155 !important;
        color: #f8fafc !important;
        border-radius: 12px;
        height: 48px;
        padding: 10px 14px;
    }

    .form-control:focus {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.18) !important;
    }

    .form-control::placeholder {
        color: #64748b;
    }

    .form-check-label {
        color: #cbd5e1;
        font-size: 14px;
    }

    .form-check-input {
        background-color: #0b1220;
        border-color: #475569;
    }

    .form-check-input:checked {
        background-color: #ef4444;
        border-color: #ef4444;
    }

    .btn-login {
        width: 100%;
        height: 48px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        font-weight: 700;
        transition: 0.2s ease;
    }

    .btn-login:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(239, 68, 68, 0.3);
        color: #fff;
    }

    .auth-link {
        color: #f87171;
        text-decoration: none;
        font-size: 14px;
    }

    .auth-link:hover {
        color: #fb7185;
        text-decoration: underline;
    }

    .invalid-feedback {
        color: #fca5a5;
        font-size: 13px;
        margin-top: 6px;
    }

    .auth-footer {
        text-align: center;
        color: #64748b;
        font-size: 12px;
        padding: 0 32px 28px;
    }

    .top-auth-brand {
        display: none;
    }
</style>

<div class="auth-page">
    <div class="auth-card">
        <div class="sidebar-header">
            <div class="sidebar-logo-medico">
                <img src="{{ asset('assets/img/logo-apotek-medico.png') }}" alt="Apotek Medico">
            </div>
        </div>

        <div class="auth-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>

                    <input
                        id="username"
                        type="text"
                        class="form-control @error('username') is-invalid @enderror"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        autofocus
                        placeholder="Masukkan username"
                    >

                    @error('username')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>

                    <input
                        id="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    >

                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >

                        <label class="form-check-label" for="remember">
                            Remember Me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">
                            Lupa Password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-login">
                    Login
                </button>
            </form>
        </div>

        <div class="auth-footer">
            © {{ date('Y') }} Apotek Medico — Sistem Informasi Apotek
        </div>
    </div>
</div>
@endsection