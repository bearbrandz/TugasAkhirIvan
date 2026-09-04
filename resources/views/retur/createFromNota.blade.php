@extends('layout.conquer')

@section('title', 'Retur Pembelian')

@section('content')
<style>
    .retur-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .retur-header h1 {
        margin: 0;
        color: #1e293b;
        font-size: 28px;
        font-weight: 800;
    }

    .retur-header p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .retur-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .retur-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .retur-info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }

    .retur-info-box small {
        display: block;
        color: #64748b;
        margin-bottom: 4px;
    }

    .retur-info-box strong {
        color: #1e293b;
    }

    .retur-table {
        width: 100%;
        color: #1e293b;
        font-size: 13px;
    }

    .retur-table th {
        background: #f8fafc;
        color: #1e293b;
        border-color: #e2e8f0;
        padding: 12px 10px;
        text-transform: uppercase;
        font-size: 12px;
    }

    .retur-table td {
        background: #ffffff;
        color: #1e293b;
        border-color: #e2e8f0;
        padding: 12px 10px;
        vertical-align: top;
    }

    .retur-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    .retur-product-title {
        font-weight: 800;
        color: #1e293b;
    }

    .retur-muted {
        display: block;
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
    }

    @media (max-width: 992px) {
        .retur-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .retur-table-wrap {
            overflow-x: auto;
        }

        .retur-table {
            min-width: 900px;
        }
    }

    @media (max-width: 576px) {
        .retur-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

<div class="retur-header">
    <div>
        <h1>Retur Pembelian</h1>
        <p>Retur barang berdasarkan nota pembelian asli.</p>
    </div>

    <a href="{{ route('retur.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="retur-card">
    <div class="retur-info-grid">
        <div class="retur-info-box">
            <small>Nota Pembelian</small>
            <strong>#{{ $nota->id }}</strong>
        </div>

        <div class="retur-info-box">
            <small>Pegawai</small>
            <strong>{{ $nota->user->nama ?? '-' }}</strong>
        </div>

        <div class="retur-info-box">
            <small>Tanggal Pembelian</small>
            <strong>{{ $nota->created_at ? \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') : '-' }}</strong>
        </div>

        <div class="retur-info-box">
            <small>Jumlah Item</small>
            <strong>{{ $items->count() }}</strong>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('retur.store') }}">
    @csrf

    <input type="hidden" name="notabelis_id" value="{{ $nota->id }}">

    <div class="retur-card">
        <div class="retur-table-wrap">
            <table class="table retur-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Batch</th>
                        <th>Distributor</th>
                        <th>Qty Beli</th>
                        <th>Stok Batch</th>
                        <th>Harga Beli</th>
                        <th>Qty Retur</th>
                        <th>Alasan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $i => $item)
                        <tr>
                            <td>
                                <span class="retur-product-title">{{ $item->nama_produk }}</span>
                                <span class="retur-muted">Satuan: {{ $item->nama_satuan ?? '-' }}</span>
                            </td>

                            <td>
                                #{{ $item->batch_id }}
                                <span class="retur-muted">
                                    Exp: {{ $item->tgl_kadaluarsa ? \Carbon\Carbon::parse($item->tgl_kadaluarsa)->format('d/m/Y') : '-' }}
                                </span>
                            </td>

                            <td>{{ $item->nama_distributor ?? '-' }}</td>

                            <td>{{ number_format($item->qty_beli ?? 0, 0, ',', '.') }}</td>

                            <td>{{ number_format($item->stok_batch ?? 0, 0, ',', '.') }}</td>

                            <td>
                                Rp {{ number_format($item->unitprice ?? 0, 0, ',', '.') }}
                            </td>

                            <td>
                                <input type="hidden" name="items[{{ $i }}][produkbatches_id]" value="{{ $item->produkbatches_id }}">
                                <input type="hidden" name="items[{{ $i }}][produks_id]" value="{{ $item->produks_id }}">
                                <input type="hidden" name="items[{{ $i }}][harga_beli]" value="{{ $item->unitprice ?? 0 }}">

                                <input
                                    type="number"
                                    name="items[{{ $i }}][qty_retur]"
                                    class="form-control"
                                    min="0"
                                    max="{{ min($item->qty_beli, $item->stok_batch) }}"
                                    value="0"
                                >

                                <small class="retur-muted">
                                    Maks: {{ min($item->qty_beli, $item->stok_batch) }}
                                </small>
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="items[{{ $i }}][alasan]"
                                    class="form-control"
                                    placeholder="Rusak / salah kirim / expired..."
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tidak ada item pembelian pada nota ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <label class="form-label">Keterangan Retur</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan retur pembelian..."></textarea>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                Simpan Retur
            </button>

            <a href="{{ route('retur.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </div>
</form>
@endsection