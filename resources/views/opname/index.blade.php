@extends('layout.conquer')

@section('title', 'Laporan Stok Opname')

@section('content')
<style>
    .opname-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .opname-page-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #f8fafc;
    }

    .opname-page-header p {
        margin: 8px 0 0;
        color: #94a3b8;
    }

    .opname-page-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .opname-filter-card {
        background: #162033;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .opname-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1.2fr auto;
        gap: 12px;
        align-items: end;
    }

    .opname-filter-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 800;
        color: #f8fafc;
        text-transform: uppercase;
    }

    .opname-table-box {
        width: 100%;
        background: #162033;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        overflow: hidden;
    }

    .opname-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        color: #f8fafc;
        font-size: 13px;
    }

    .opname-table thead th {
        background: #1e2b42;
        color: #f8fafc;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        border-color: rgba(148, 163, 184, 0.14);
        padding: 12px 10px;
        vertical-align: middle;
    }

    .opname-table tbody td,
    .opname-table tfoot td {
        background: #162033;
        color: #f8fafc;
        border-color: rgba(148, 163, 184, 0.12);
        padding: 12px 10px;
        vertical-align: top;
    }

    .opname-table tbody tr:nth-child(even) td {
        background: #1b2638;
    }

    .opname-table tfoot td {
        background: #1e2b42;
        font-weight: 800;
    }

    .col-no {
        width: 4%;
        text-align: center;
    }

    .col-tanggal {
        width: 9%;
    }

    .col-batch {
        width: 16%;
    }

    .col-produk {
        width: 18%;
    }

    .col-gudang {
        width: 15%;
    }

    .col-stok {
        width: 11%;
    }

    .col-selisih {
        width: 12%;
    }

    .col-aksi {
        width: 9%;
        text-align: center;
    }

    .cell-main {
        display: block;
        font-weight: 800;
        color: #f8fafc;
        line-height: 1.35;
        word-break: break-word;
    }

    .cell-sub {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.35;
        word-break: break-word;
    }

    .stock-main {
        display: block;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    .stock-value {
        display: block;
        margin-top: 5px;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.35;
    }

    .selisih-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 52px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
    }

    .selisih-plus {
        background: rgba(34, 197, 94, 0.18);
        color: #22c55e;
    }

    .selisih-minus {
        background: rgba(239, 68, 68, 0.18);
        color: #ef4444;
    }

    .selisih-zero {
        background: rgba(148, 163, 184, 0.16);
        color: #cbd5e1;
    }

    .nilai-plus {
        color: #34d399;
        font-weight: 800;
    }

    .nilai-minus {
        color: #f87171;
        font-weight: 800;
    }

    .aksi-stack {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: center;
    }

    .aksi-stack .btn {
        width: 82px;
        min-height: 34px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 800;
        border-radius: 6px;
    }

    .opname-empty {
        padding: 28px;
        text-align: center;
        color: #94a3b8;
    }

    @media (max-width: 1200px) {
        .opname-filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .opname-table-box {
            overflow-x: auto;
        }

        .opname-table {
            min-width: 1050px;
        }
    }

    @media (max-width: 768px) {
        .opname-page-header {
            flex-direction: column;
        }

        .opname-page-actions {
            width: 100%;
        }

        .opname-page-actions .btn {
            width: 100%;
        }

        .opname-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="opname-page-header">
    <div>
        <h1>Laporan Stok Opname Barang</h1>
        <p>Laporan hasil pencocokan stok sistem dengan stok fisik per batch produk.</p>
    </div>

    <div class="opname-page-actions">
        <a href="{{ route('opnames.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Buat Stok Opname
        </a>

        <a
            href="{{ route('opnames.csv', [
                'search' => $search ?? '',
                'tanggal_mulai' => $tanggalMulai ?? '',
                'tanggal_sampai' => $tanggalSampai ?? '',
            ]) }}"
            class="btn btn-success"
        >
            <i class="fa fa-download"></i> Export CSV
        </a>
    </div>
</div>

<div class="opname-filter-card">
    <form method="GET" action="{{ route('opnames.index') }}">
        <div class="opname-filter-grid">
            <div class="opname-filter-group">
                <label>Cari Data</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari produk, batch, gudang, petugas..."
                    value="{{ $search ?? '' }}"
                >
            </div>

            <div class="opname-filter-group">
                <label>Tanggal Mulai</label>
                <input
                    type="date"
                    name="tanggal_mulai"
                    class="form-control"
                    value="{{ $tanggalMulai ?? '' }}"
                >
            </div>

            <div class="opname-filter-group">
                <label>Tanggal Sampai</label>
                <input
                    type="date"
                    name="tanggal_sampai"
                    class="form-control"
                    value="{{ $tanggalSampai ?? '' }}"
                >
            </div>

            <div class="opname-filter-group">
                <button type="submit" class="btn btn-primary w-100">
                    Tampilkan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="opname-table-box">
    <table class="table opname-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-batch">Batch</th>
                <th class="col-produk">Produk</th>
                <th class="col-gudang">Gudang / Petugas</th>
                <th class="col-stok">Stok Sistem</th>
                <th class="col-stok">Stok Fisik</th>
                <th class="col-selisih">Selisih</th>
                <th class="col-aksi">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($datas as $d)
                @php
                    $stokSistem = (float) ($d->stok_sistem ?? 0);
                    $stokFisik = (float) ($d->stok_fisik ?? 0);
                    $selisih = (float) ($d->selisih ?? ($stokFisik - $stokSistem));

                    $hargaPokok = (float) ($d->harga_pokok ?? 0);

                    $nilaiSistem = (float) ($d->nilai_stok_sistem ?? ($stokSistem * $hargaPokok));
                    $nilaiFisik = (float) ($d->nilai_stok_fisik ?? ($stokFisik * $hargaPokok));
                    $nilaiSelisih = (float) ($d->nilai_selisih ?? ($selisih * $hargaPokok));

                    $badgeClass = 'selisih-zero';

                    if ($selisih > 0) {
                        $badgeClass = 'selisih-plus';
                    }

                    if ($selisih < 0) {
                        $badgeClass = 'selisih-minus';
                    }
                @endphp

                <tr>
                    <td class="col-no">
                        {{ $loop->iteration + (($datas->currentPage() - 1) * $datas->perPage()) }}
                    </td>

                    <td class="col-tanggal">
                        <span class="cell-main">
                            {{ $d->tanggal ? \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y') : '-' }}
                        </span>
                    </td>

                    <td class="col-batch">
                        <span class="cell-main">
                            {{ $d->kode_barang ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Batch ID: {{ $d->batch_id ?? '-' }}
                        </span>
                    </td>

                    <td class="col-produk">
                        <span class="cell-main">
                            {{ $d->nama_produk ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Satuan: {{ $d->nama_satuan ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Harga Pokok: Rp {{ number_format($hargaPokok, 0, ',', '.') }}
                        </span>
                    </td>

                    <td class="col-gudang">
                        <span class="cell-main">
                            {{ $d->lokasi_gudang ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Petugas: {{ $d->nama_user ?? '-' }}
                        </span>

                        @if (!empty($d->keterangan))
                            <span class="cell-sub">
                                Ket: {{ $d->keterangan }}
                            </span>
                        @endif
                    </td>

                    <td class="col-stok">
                        <span class="stock-main">
                            {{ number_format($stokSistem, 0, ',', '.') }}
                        </span>

                        <span class="stock-value">
                            Rp {{ number_format($nilaiSistem, 0, ',', '.') }}
                        </span>
                    </td>

                    <td class="col-stok">
                        <span class="stock-main">
                            {{ number_format($stokFisik, 0, ',', '.') }}
                        </span>

                        <span class="stock-value">
                            Rp {{ number_format($nilaiFisik, 0, ',', '.') }}
                        </span>
                    </td>

                    <td class="col-selisih">
                        <span class="selisih-badge {{ $badgeClass }}">
                            {{ $selisih > 0 ? '+' : '' }}{{ number_format($selisih, 0, ',', '.') }}
                        </span>

                        <span class="stock-value {{ $nilaiSelisih < 0 ? 'nilai-minus' : ($nilaiSelisih > 0 ? 'nilai-plus' : '') }}">
                            {{ $nilaiSelisih < 0 ? '- ' : ($nilaiSelisih > 0 ? '+ ' : '') }}
                            Rp {{ number_format(abs($nilaiSelisih), 0, ',', '.') }}
                        </span>
                    </td>

                    <td class="col-aksi">
                        <div class="aksi-stack">
                            <a
                                class="btn btn-warning btn-sm"
                                href="{{ route('opnames.edit', $d->id) }}"
                            >
                                Edit
                            </a>

                            <form
                                method="POST"
                                action="{{ route('opnames.destroy', $d->id) }}"
                                onsubmit="return confirm('Hapus data stok opname ini?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="opname-empty">
                            Belum ada data stok opname.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if ($datas->count() > 0)
            @php
                $rows = collect($datas->items());

                $totalStokSistem = $rows->sum(fn ($x) => (float) ($x->stok_sistem ?? 0));
                $totalStokFisik = $rows->sum(fn ($x) => (float) ($x->stok_fisik ?? 0));
                $totalSelisih = $rows->sum(fn ($x) => (float) ($x->selisih ?? (($x->stok_fisik ?? 0) - ($x->stok_sistem ?? 0))));

                $totalNilaiSistem = $rows->sum(fn ($x) => (float) ($x->nilai_stok_sistem ?? 0));
                $totalNilaiFisik = $rows->sum(fn ($x) => (float) ($x->nilai_stok_fisik ?? 0));
                $totalNilaiSelisih = $rows->sum(fn ($x) => (float) ($x->nilai_selisih ?? 0));
            @endphp

            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right;">
                        Total
                    </td>

                    <td>
                        <span class="stock-main">
                            {{ number_format($totalStokSistem, 0, ',', '.') }}
                        </span>
                        <span class="stock-value">
                            Rp {{ number_format($totalNilaiSistem, 0, ',', '.') }}
                        </span>
                    </td>

                    <td>
                        <span class="stock-main">
                            {{ number_format($totalStokFisik, 0, ',', '.') }}
                        </span>
                        <span class="stock-value">
                            Rp {{ number_format($totalNilaiFisik, 0, ',', '.') }}
                        </span>
                    </td>

                    <td>
                        <span class="stock-main">
                            {{ $totalSelisih > 0 ? '+' : '' }}{{ number_format($totalSelisih, 0, ',', '.') }}
                        </span>

                        <span class="stock-value {{ $totalNilaiSelisih < 0 ? 'nilai-minus' : ($totalNilaiSelisih > 0 ? 'nilai-plus' : '') }}">
                            {{ $totalNilaiSelisih < 0 ? '- ' : ($totalNilaiSelisih > 0 ? '+ ' : '') }}
                            Rp {{ number_format(abs($totalNilaiSelisih), 0, ',', '.') }}
                        </span>
                    </td>

                    <td>-</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<div class="mt-4">
    {{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection