@extends('layouts.app')

@section('content')
<style>
    body {
        background: #ffffff !important;
        color: #1e293b;
        font-family: 'Inter', sans-serif;
    }

    .auth-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
        background-image: url('{{ asset('assets/img/foto-bg-lp.JPG') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }

    .auth-card {
        width: 100%;
        max-width: 460px;
        background: rgba(234, 243, 252, 0.9);
        border: 1px solid #BFDBFE;
        border-radius: 22px;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.1);
        overflow: hidden;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .auth-card .sidebar-header {
        background: #0B1D3A;
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
        color: #1e293b;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .auth-header p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .auth-body {
        padding: 10px 32px 34px;
    }

    .form-label {
        color: #334155;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-control {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
        border-radius: 12px;
        height: 48px;
        padding: 10px 14px;
    }

    .form-control:focus {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.18) !important;
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .form-check-label {
        color: #334155;
        font-size: 14px;
    }

    .form-check-input {
        background-color: #ffffff;
        border-color: #94a3b8;
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
        color: #dc2626;
        text-decoration: none;
        font-size: 14px;
    }

    .auth-link:hover {
        color: #b91c1c;
        text-decoration: underline;
    }

    .invalid-feedback {
        color: #dc2626;
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

                <div class="mb-4">
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