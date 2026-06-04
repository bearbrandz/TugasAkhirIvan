@extends('layout.conquer')

@section('title', 'Produk per Gudang')

@section('content')
<div class="am-page-header">
    <div>
        <h1>Produk di {{ $gudang->lokasi ?? 'Gudang' }}</h1>
        <p>Daftar produk dan batch yang tersimpan pada lokasi/rak ini.</p>
    </div>

    <a href="{{ route('gudangs.index') }}" class="btn btn-default">
        Kembali
    </a>
</div>

<div class="am-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Batch</th>
                <th>Produk</th>
                <th>Golongan</th>
                <th>Stok</th>
                <th>Harga Beli/HPP</th>
                <th>Distributor</th>
                <th>Expired</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td>#{{ $d->batch_id }}</td>
                    <td><strong>{{ $d->nama_produk }}</strong></td>
                    <td>{{ ucfirst($d->golongan ?? '-') }}</td>
                    <td>
                        {{ number_format($d->stok, 0, ',', '.') }}
                        {{ $d->nama_satuan ?? '' }}
                    </td>
                    <td>
                        Rp {{ number_format($d->hpp_avg_per_unit ?: $d->unitprice, 0, ',', '.') }}
                    </td>
                    <td>{{ $d->nama_distributor ?? '-' }}</td>
                    <td>
                        {{ $d->tgl_kadaluarsa ? \Carbon\Carbon::parse($d->tgl_kadaluarsa)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ ucfirst($d->status ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        Belum ada produk pada gudang/rak ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $datas->links('pagination::bootstrap-5') }}
</div>
@endsection
