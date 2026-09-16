@extends('layout.conquer')

@section('title', 'Produk dari Distributor')

@section('content')
<div class="am-page-header">
    <div>
        <h1><i class="icon-share" style="margin-right:8px;color:#3b82f6;"></i>Produk dari {{ $distributor->nama ?? 'Distributor' }}</h1>
        <p>Daftar lengkap produk dan batch yang pernah disuplai oleh distributor ini.</p>
    </div>

    <a href="{{ route('distributors.index') }}" class="btn btn-default">
        Kembali
    </a>
</div>

<div class="am-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Batch</th>
                <th>Nama Produk</th>
                <th>Golongan</th>
                <th>Stok Tersisa</th>
                <th>Harga Beli/HPP</th>
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
                        <span class="am-badge am-badge-tersedia" style="margin-bottom:0px;">
                            {{ number_format($d->stok, 0, ',', '.') }} {{ $d->nama_satuan ?? '' }}
                        </span>
                    </td>
                    <td>Rp {{ number_format($d->hpp_avg_per_unit ?: $d->unitprice, 0, ',', '.') }}</td>
                    <td>{{ $d->tgl_kadaluarsa ? \Carbon\Carbon::parse($d->tgl_kadaluarsa)->format('d/m/Y') : '-' }}</td>
                    <td>{{ ucfirst($d->status ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding:30px; color:#94a3b8;">
                        Belum ada produk yang disuplai oleh distributor ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">{{ $datas->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
