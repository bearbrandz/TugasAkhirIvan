@extends('layout.conquer')
@section('title')
@section('content')

@if (session('status'))
    <div class="am-alert am-alert-success">{{ session('status') }}</div>
@endif

<div class="am-page-header">
    <div>
        <h1><i class="icon-share" style="margin-right:8px;color:#3b82f6;"></i>Daftar Distributor</h1>
        <p>Kelola data pemasok dan distributor obat</p>
    </div>
    <div>
        <a href="{{ route('distributors.create') }}" class="btn btn-primary" style="margin-right: 8px;">
            <i class="fa fa-plus"></i> Tambah Pemasok
        </a>
        <a href="{{ route('distributors.arsip') }}" class="btn btn-default">
            <i class="fa fa-trash"></i> Lihat Arsip
        </a>
    </div>
</div>

<div class="am-table-wrap">
    <div class="am-table-toolbar">
        <form method="GET" action="{{ route('distributors.index') }}" class="am-search-bar">
            <input type="text" name="search" placeholder="Cari distributor..." value="{{ $search }}">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            @if($search)
                <a href="{{ route('distributors.index') }}" class="btn btn-default btn-sm">Reset</a>
            @endif
        </form>
        <span class="text-muted" style="font-size:13px;">{{ $datas->total() }} distributor</span>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Nama Distributor</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Total Produk</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td><strong>{{ $d->nama }}</strong></td>
                    <td>{{ $d->alamat ?? '-' }}</td>
                    <td>{{ $d->no_hp ?? '-' }}</td>
                    <td>
                        <span class="am-badge am-badge-tersedia">
                            {{ $d->produkbatches_count ?? 0 }} batch
                        </span>
                    </td>
                    <td>
                        <div class="am-action-btns">
                            <a href="{{ route('distributors.edit', $d->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('distributors.destroy', $d->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus distributor {{ addslashes($d->nama) }}?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">
                    <div class="am-empty">
                        <i class="icon-share"></i>
                        <p>Belum ada distributor yang terdaftar.</p>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
