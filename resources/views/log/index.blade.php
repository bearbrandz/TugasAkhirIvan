@extends('layout.conquer')
@section('title')
@section('content')

<div class="am-page-header">
    <div>
        <h1><i class="icon-list" style="margin-right:8px;color:#3b82f6;"></i>Log Aktivitas Sistem</h1>
        <p>Riwayat seluruh aktivitas pengguna yang tercatat oleh sistem</p>
    </div>
</div>

<div class="am-table-wrap">
    <div class="am-table-toolbar" style="flex-direction:column; align-items:flex-start; gap:10px;">
        <form method="GET" action="{{ route('log.index') }}" style="display:flex; gap:8px; flex-wrap:wrap; width:100%;">
            <input type="text" name="search" class="form-control" style="max-width:220px;"
                placeholder="Cari user / deskripsi..." value="{{ $search }}">
            <select name="modul" class="form-control" style="max-width:180px;">
                <option value="">Semua Modul</option>
                @foreach($moduls as $m)
                    <option value="{{ $m }}" {{ $modul == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" class="form-control" style="max-width:150px;"
                value="{{ $startDate }}" title="Dari tanggal">
            <input type="date" name="end_date" class="form-control" style="max-width:150px;"
                value="{{ $endDate }}" title="Sampai tanggal">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('log.index', ['reset' => 1]) }}" class="btn btn-default btn-sm">Reset</a>
        </form>
        <span class="text-muted" style="font-size:13px;">{{ $datas->total() }} aktivitas ditemukan</span>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Modul</th>
                <th>Aksi</th>
                <th>Deskripsi</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $log)
                <tr>
                    <td style="white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}<br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</small>
                    </td>
                    <td><strong>{{ $log->nama_user ?? '-' }}</strong></td>
                    <td>
                        @php
                            $roleColor = [
                                'admin'    => 'am-badge-narkotika',
                                'apoteker' => 'am-badge-terbatas',
                                'kasir'    => 'am-badge-tersedia',
                            ][$log->tipe_user] ?? 'am-badge-bebas';
                        @endphp
                        <span class="am-badge {{ $roleColor }}">{{ ucfirst($log->tipe_user ?? '-') }}</span>
                    </td>
                    <td>
                        <span class="am-badge am-badge-proses">{{ $log->modul }}</span>
                    </td>
                    <td>
                        @php
                            $aksiIcon = [
                                'login'             => 'fa-sign-in',
                                'logout'            => 'fa-sign-out',
                                'pembelian_baru'    => 'fa-shopping-cart',
                                'retur_pembelian'   => 'fa-undo',
                                'tambah_produk'     => 'fa-plus-circle',
                                'edit_produk'       => 'fa-pencil',
                                'hapus_produk'      => 'fa-trash',
                            ];
                            $icon = $aksiIcon[$log->aksi] ?? 'fa-circle';
                        @endphp
                        <i class="fa {{ $icon }}" style="margin-right:4px;"></i>
                        {{ str_replace('_', ' ', ucfirst($log->aksi)) }}
                    </td>
                    <td style="font-size:12.5px; max-width:300px;">{{ $log->deskripsi ?? '-' }}</td>
                    <td><code style="font-size:11px;">{{ $log->ip_address ?? '-' }}</code></td>
                </tr>
            @empty
                <tr><td colspan="7">
                    <div class="am-empty">
                        <i class="icon-list"></i>
                        <p>Belum ada aktivitas yang tercatat.</p>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
