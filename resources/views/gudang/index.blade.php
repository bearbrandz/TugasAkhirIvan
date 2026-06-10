@extends('layout.conquer')
@section('title')
@section('content')

@if (session('status'))
    <div class="am-alert am-alert-success">{{ session('status') }}</div>
@endif

<div class="am-page-header">
    <div>
        <h1><i class="icon-briefcase" style="margin-right:8px;color:#3b82f6;"></i>Lokasi Penyimpanan</h1>
        <p>Kelola lokasi gudang dan rak penyimpanan obat</p>
    </div>
    <div>
        <a href="{{ route('gudangs.create') }}" class="btn btn-primary" style="margin-right: 8px;">
            <i class="fa fa-plus"></i> Tambah Rak
        </a>
        <a href="{{ route('gudangs.arsip') }}" class="btn btn-default">
            <i class="fa fa-trash"></i> Lihat Arsip
        </a>
    </div>
</div>

<div class="am-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Lokasi / Nama Gudang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td>{{ $datas->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $d->lokasi }}</strong></td>
                    <td>
                        <div class="am-action-btns">
                            <a href="{{ route('gudangs.edit', $d->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <a href="{{ route('gudangs.produk', $d->id) }}" class="btn btn-info btn-sm">
                                Lihat Produk
                            </a>
                            <form method="POST" action="{{ route('gudangs.destroy', $d->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus lokasi ini?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">
                    <div class="am-empty"><i class="icon-briefcase"></i><p>Belum ada lokasi gudang.</p></div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 15px;">
    {{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection
