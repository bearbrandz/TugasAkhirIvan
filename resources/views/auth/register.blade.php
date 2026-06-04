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
        max-width: 520px;
        background: #111827;
        border: 1px solid #26354d;
        border-radius: 22px;
        box-shadow: 0 22px 70px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }

    .auth-header {
        padding: 32px 32px 8px;
        text-align: center;
    }

    .auth-header h1 {
        margin: 18px 0 0;
        color: #fff !important;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .auth-header p {
        margin: 8px 0 0;
        color: #94a3b8 !important;
        font-size: 14px;
    }

    .auth-body {
        padding: 20px 32px 34px;
    }

    .reg-label {
        display: block;
        color: #e5e7eb !important;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .reg-input {
        width: 100%;
        background: #0b1220 !important;
        border: 1px solid #334155 !important;
        color: #f8fafc !important;
        border-radius: 12px;
        height: 48px;
        padding: 10px 14px;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .reg-input:focus {
        outline: none;
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.18) !important;
    }

    .reg-input::placeholder {
        color: #64748b;
    }

    select.reg-input {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
        cursor: pointer;
    }

    select.reg-input option {
        background: #111827;
        color: #f8fafc;
    }

    .reg-group {
        margin-bottom: 18px;
    }

    .reg-row {
        display: flex;
        gap: 14px;
    }

    .reg-row .reg-col {
        flex: 1;
    }

    .btn-register {
        width: 100%;
        height: 48px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: 0.2s ease;
        margin-top: 6px;
    }

    .btn-register:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(239, 68, 68, 0.3);
        color: #fff;
    }

    .btn-cancel {
        display: block;
        width: 100%;
        height: 48px;
        line-height: 48px;
        border: 1px solid #334155;
        border-radius: 12px;
        background: transparent;
        color: #cbd5e1 !important;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: 0.2s ease;
        margin-top: 10px;
    }

    .btn-cancel:hover {
        background: #1e293b;
        color: #fff !important;
        text-decoration: none;
    }

    .invalid-feedback {
        color: #fca5a5 !important;
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

        <div class="auth-header">
            <h1>Register User Baru</h1>
            <p>Silakan isi form di bawah untuk membuat akun</p>
        </div>

        <div class="auth-body">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="reg-group">
                    <label for="nama" class="reg-label">Nama Lengkap</label>
                    <input
                        id="nama"
                        type="text"
                        class="reg-input @error('nama') is-invalid @enderror"
                        name="nama"
                        value="{{ old('nama') }}"
                        required
                        autocomplete="nama"
                        autofocus
                        placeholder="Masukkan nama lengkap"
                    >
                    @error('nama')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- No HP --}}
                <div class="reg-group">
                    <label for="no_hp" class="reg-label">No HP</label>
                    <input
                        id="no_hp"
                        type="text"
                        class="reg-input @error('no_hp') is-invalid @enderror"
                        name="no_hp"
                        value="{{ old('no_hp') }}"
                        required
                        autocomplete="no_hp"
                        placeholder="Contoh: 08123456789"
                    >
                    @error('no_hp')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="reg-group">
                    <label for="email" class="reg-label">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        class="reg-input @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="Masukkan alamat email aktif"
                    >
                    @error('email')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="reg-group">
                    <label for="username" class="reg-label">Username</label>
                    <input
                        id="username"
                        type="text"
                        class="reg-input @error('username') is-invalid @enderror"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        placeholder="Masukkan username"
                    >
                    @error('username')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Password & Konfirmasi (side by side) --}}
                <div class="reg-group">
                    <div class="reg-row">
                        <div class="reg-col">
                            <label for="password" class="reg-label">Password</label>
                            <input
                                id="password"
                                type="password"
                                class="reg-input @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Password"
                            >
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="reg-col">
                            <label for="password-confirm" class="reg-label">Konfirmasi Password</label>
                            <input
                                id="password-confirm"
                                type="password"
                                class="reg-input"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Konfirmasi"
                            >
                        </div>
                    </div>
                </div>

                {{-- Tipe User / Role --}}
                <div class="reg-group">
                    <label for="tipe_user" class="reg-label">Tipe User / Role</label>
                    <select
                        id="tipe_user"
                        class="reg-input @error('tipe_user') is-invalid @enderror"
                        name="tipe_user"
                        required
                    >
                        <option value="admin" {{ old('tipe_user') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="apoteker" {{ old('tipe_user') == 'apoteker' ? 'selected' : '' }}>Apoteker</option>
                        <option value="kasir" {{ old('tipe_user') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    </select>
                    @error('tipe_user')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Buttons --}}
                <button type="submit" class="btn-register">
                    Daftarkan Akun
                </button>
                <a href="{{ route('users.index') }}" class="btn-cancel">
                    Batal
                </a>
            </form>
        </div>

        <div class="auth-footer">
            © {{ date('Y') }} Apotek Medico — Sistem Informasi Apotek
        </div>
    </div>
</div>
@endsection
