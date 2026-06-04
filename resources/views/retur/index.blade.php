@extends('layout.conquer')
@section('title')
@section('content')

@if (session('status'))
    <div class="am-alert am-alert-success">{{ session('status') }}</div>
@endif

<div class="am-page-header">
    <div>
        <h1><i class="icon-action-undo" style="margin-right:8px;color:#ef4444;"></i>Daftar Retur Pembelian</h1>
        <p>Kelola pengembalian barang kepada distributor</p>
    </div>
</div>

<div class="am-table-wrap">
    <div class="am-table-toolbar">
        <form method="GET" action="{{ route('retur.index') }}" class="am-search-bar">
            <input type="text" name="search" placeholder="Cari nota / alasan..." value="{{ $search }}">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            @if($search)
                <a href="{{ route('retur.index') }}" class="btn btn-default btn-sm">Reset</a>
            @endif
        </form>
        <span class="text-muted" style="font-size:13px;">{{ $datas->total() }} retur tercatat</span>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No. Retur</th>
                <th>Nota Beli Asal</th>
                <th>Tanggal Retur</th>
                <th>Obat yang Diretur</th>
                <th>Keterangan</th>
                <th>Total Nilai Retur</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td><strong>{{ $d->no_retur ?: '#' . $d->id }}</strong></td>
                    <td>Nota #{{ $d->notabelis_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->tanggal_retur)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $produkNames = $d->items->map(function($item) {
                                return $item->produk ? $item->produk->nama : '-';
                            })->unique()->implode(', ');
                        @endphp
                        {{ $produkNames }}
                    </td>
                    <td>{{ $d->keterangan ?? '-' }}</td>
                    <td><strong>Rp {{ number_format($d->total, 0, ',', '.') }}</strong></td>
                    <td>
                        <div class="am-action-btns">
                            <a href="{{ route('retur.show', $d->id) }}" class="btn btn-info btn-sm">
                                <i class="fa fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('retur.print', $d->id) }}" class="btn btn-default btn-sm" target="_blank">
                                <i class="fa fa-print"></i> Print
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">
                    <div class="am-empty">
                        <i class="icon-action-undo"></i>
                        <p>Belum ada retur pembelian yang dicatat.</p>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
    <div>{{ $datas->appends(request()->query())->links() }}</div>
</div>
@endsection
