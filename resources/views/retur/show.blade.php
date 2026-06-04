@extends('layout.conquer')

@section('title', 'Detail Retur Pembelian')

@section('content')
@php
    $returItems = collect($items ?? ($retur->items ?? []));

    $tanggalRetur = $retur->tanggal_retur
        ?? $retur->tgl_retur
        ?? $retur->created_at
        ?? now();

    $totalRetur = $retur->total_retur
        ?? $retur->total
        ?? $returItems->sum('subtotal');

    $notaBeliId = $retur->notabelis_id
        ?? $retur->nota_pembelian_id
        ?? '-';
@endphp

<div class="am-page-header">
    <div>
        <h1>
            <i class="icon-action-undo" style="margin-right:8px;color:#ef4444;"></i>
            Detail Retur {{ $retur->no_retur ?: '#' . $retur->id }}
        </h1>

        <p>
            {{ \Carbon\Carbon::parse($tanggalRetur)->format('d/m/Y') }}
            &mdash;
            Nota Beli #{{ $notaBeliId }}
        </p>
    </div>

    <div class="am-action-btns">
        <a href="{{ route('retur.print', $retur->id) }}" class="btn btn-default" target="_blank">
            <i class="fa fa-print"></i> Print
        </a>

        <a href="{{ route('retur.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="am-form-card" style="margin-bottom:16px;">
            <div class="am-form-section-title">Informasi Retur</div>

            <table class="table table-condensed" style="margin:0;">
                <tr>
                    <td class="text-muted" width="140">No. Retur</td>
                    <td><strong>{{ $retur->no_retur ?: '#' . $retur->id }}</strong></td>
                </tr>

                <tr>
                    <td class="text-muted">Tanggal Retur</td>
                    <td>{{ \Carbon\Carbon::parse($tanggalRetur)->format('d F Y') }}</td>
                </tr>

                <tr>
                    <td class="text-muted">Nota Beli Asal</td>
                    <td>#{{ $notaBeliId }}</td>
                </tr>

                <tr>
                    <td class="text-muted">Pegawai</td>
                    <td>{{ $retur->nama_pegawai ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="text-muted">Alasan</td>
                    <td>{{ $retur->alasan ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="text-muted">Keterangan</td>
                    <td>{{ $retur->keterangan ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="am-stat-card red">
            <div class="label">Total Nilai Retur</div>

            <div class="value">
                Rp {{ number_format($totalRetur, 0, ',', '.') }}
            </div>

            <div class="sub">
                {{ $returItems->count() }} item diretur
            </div>
        </div>
    </div>
</div>

<div class="am-table-wrap">
    <div class="am-table-toolbar">
        <strong style="font-size:14px;">Item yang Diretur</strong>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Batch</th>
                <th>Distributor</th>
                <th>Satuan</th>
                <th>Qty Diretur</th>
                <th>Harga/Unit</th>
                <th>Subtotal</th>
                <th>Alasan Item</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($returItems as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>

                    <td>
                        <strong>{{ $item->nama_produk ?? '-' }}</strong>
                    </td>

                    <td>
                        #{{ $item->batch_id ?? $item->produkbatches_id ?? '-' }}
                    </td>

                    <td>
                        {{ $item->nama_distributor ?? '-' }}
                    </td>

                    <td>
                        {{ $item->nama_satuan ?? '-' }}
                    </td>

                    <td>
                        {{ number_format($item->qty_diretur ?? $item->qty ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}
                    </td>

                    <td>
                        <strong>
                            Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                        </strong>
                    </td>

                    <td>
                        {{ $item->alasan ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        Belum ada item retur.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="7" class="text-right">
                    <strong>Total:</strong>
                </td>

                <td colspan="2">
                    <strong style="color:#ef4444;">
                        Rp {{ number_format($totalRetur, 0, ',', '.') }}
                    </strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection