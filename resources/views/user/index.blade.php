@extends('layout.conquer')

@section('title', 'Manajemen Karyawan')

@section('content')

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="am-page-header">
    <div>
        <h1>
            <i class="icon-user" style="margin-right:8px;color:#3b82f6;"></i>
            Manajemen Karyawan
        </h1>
        <p>Kelola akun dan hak akses pengguna sistem</p>
    </div>

    <div>
        <a href="{{ route('registerUser') }}" class="btn btn-primary" style="margin-right: 8px;">
            <i class="fa fa-plus"></i> Tambah Karyawan Baru
        </a>
        <a href="{{ route('users.arsip') }}" class="btn btn-default">
            <i class="fa fa-trash"></i> Lihat Arsip
        </a>
    </div>
</div>

<div class="am-table-wrap">
    <div class="am-table-toolbar">
        <form method="GET" action="{{ route('user') }}" class="am-search-bar">
            <input
                type="text"
                name="search"
                placeholder="Cari nama atau username..."
                value="{{ $search ?? '' }}"
            >
            <button type="submit" class="btn btn-primary btn-sm">
                Cari
            </button>
        </form>

        <span class="text-muted" style="font-size:13px;">
            {{ $datas->total() }} karyawan
        </span>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td>
                        <strong>{{ $d->nama }}</strong>
                    </td>

                    <td>
                        <code>{{ $d->username }}</code>
                    </td>

                    <td>
                        {{ $d->email ?? '-' }}
                    </td>

                    <td>
                        @php
                            $roleColor = [
                                'admin'    => 'am-badge-narkotika',
                                'apoteker' => 'am-badge-terbatas',
                                'kasir'    => 'am-badge-tersedia',
                            ][$d->tipe_user] ?? 'am-badge-bebas';
                        @endphp

                        <span class="am-badge {{ $roleColor }}">
                            {{ ucfirst($d->tipe_user) }}
                        </span>
                    </td>

                    <td>
                        {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') : '-' }}
                    </td>

                    <td>
                        <div class="am-action-btns">
                            <a href="{{ route('users.edit', $d->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-pencil"></i> Edit
                            </a>

                            @if(auth()->id() !== $d->id)
                                <form method="POST" action="{{ route('users.destroy', $d->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus karyawan {{ addslashes($d->nama) }}?')"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted" style="font-size:12px;">
                                    Akun aktif
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="am-empty">
                            <i class="icon-user"></i>
                            <p>Belum ada karyawan.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $datas->appends(request()->query())->links() }}
    </div>
</div>

@endsection