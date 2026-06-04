@extends('layout.conquer')

@section('title', 'Daftar Nota Penjualan Narkotika dan Psikotropika')

@section('content')
<style>
    .narkotika-search {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .narkotika-search input {
        flex: 1;
        min-width: 0;
    }

    .narkotika-filter-row {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .narkotika-help {
        color: #9ca3af;
        font-size: 13px;
        margin: 6px 0 14px;
    }

    .table-narkotika {
        width: 100%;
        table-layout: fixed;
    }

    .table-narkotika th,
    .table-narkotika td {
        vertical-align: top !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
        font-size: 13px;
        line-height: 1.45;
        padding: 12px 12px !important;
    }

    .table-narkotika th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .col-nota { width: 13%; }
    .col-obat { width: 27%; }
    .col-pemakaian { width: 18%; }
    .col-pasien { width: 32%; }
    .col-aksi { width: 10%; text-align: center; }

    .cell-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .cell-sub {
        display: block;
        color: #cbd5e1;
        font-size: 12px;
        margin-top: 2px;
    }

    .pemakaian-box {
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: 8px;
        padding: 8px 10px;
        display: inline-block;
        min-width: 120px;
    }

    .pemakaian-label {
        display: block;
        color: #9ca3af;
        font-size: 11px;
        margin-bottom: 2px;
    }

    .pemakaian-value {
        font-weight: 700;
        font-size: 15px;
    }

    .btn-cetak-narkotika {
        min-width: 72px;
        padding: 8px 8px;
        font-size: 12px;
        line-height: 1.3;
    }
</style>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="am-page-header">
    <div>
        <h1>Daftar Nota Penjualan Narkotika dan Psikotropika</h1>
        <p>Daftar transaksi pemakaian obat narkotika dan psikotropika dari racikan.</p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('racikans.reportNarkotika') }}"
            style="background: linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff; padding: 10px 18px; border-radius:8px; font-weight:700; font-size:13px; text-decoration:none; display:flex; align-items:center; gap:8px; border:none;">
            <i class="fas fa-file-export"></i> Ekspor SIPNAP &amp; SIMONA
        </a>
        <a href="{{ route('racikan') }}" class="btn btn-primary">
            Create New Nota Penjualan
        </a>
    </div>
</div>

<div class="container-fluid" style="padding-left:0;padding-right:0;">
    <form method="GET" action="{{ route('racikans.daftarNarkotika') }}" class="narkotika-search">
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Cari racikan, obat, pasien, dokter, distributor..."
            value="{{ $search ?? '' }}"
        >

        <button type="submit" class="btn btn-primary">
            Search
        </button>
    </form>

    <form method="GET" action="{{ route('racikans.reportNarkotika') }}" class="narkotika-filter-row">
        <label for="groupBy" style="margin:0;">Lihat Laporan Narkotika:</label>

        <select name="filter" id="groupBy" class="form-select w-auto">
            <option value="">-- Pilih Grup --</option>
            <option value="day">Hari Ini</option>
            <option value="week">Minggu Ini</option>
            <option value="month">Bulan Ini</option>
            <option value="year">Tahun Ini</option>
        </select>

        <button type="submit" class="btn btn-primary">
            Lihat
        </button>
    </form>

    <div class="narkotika-help">
        <strong>Masuk Periode</strong> berarti stok narkotika/psikotropika yang masuk dari pembelian pada periode laporan.
        <strong>Jumlah Dipakai</strong> berarti stok yang keluar untuk racikan.
    </div>
    
    <div class="am-table-wrap">
    <table class="table table-striped table-narkotika">
        <thead>
            <tr>
                <th class="col-nota">Nota / Racikan</th>
                <th class="col-obat">Obat / Batch</th>
                <th class="col-pemakaian">Pemakaian</th>
                <th class="col-pasien">Pasien / Dokter</th>
                <th class="col-aksi">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($datas as $d)
                @php
                    $printId = $d->notajual_id ?? $d->racikan_id ?? null;
                @endphp

                <tr>
                    <td class="col-nota">
                        @if (!empty($d->notajual_id))
                            <div class="cell-title">NJ #{{ $d->notajual_id }}</div>
                        @else
                            <div class="cell-title">Racikan #{{ $d->racikan_id ?? '-' }}</div>
                        @endif

                        <span class="cell-sub">
                            Racikan: {{ $d->racikan_id ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Batch: {{ $d->batch_id ?? '-' }}
                        </span>
                    </td>

                    <td class="col-obat">
                        <div class="cell-title">{{ $d->nama_produk ?? '-' }}</div>

                        <span class="cell-sub">
                            Satuan: {{ $d->nama_satuan ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Distributor: {{ $d->nama_distributor ?? '-' }}
                        </span>
                    </td>

                    <td class="col-pemakaian">
                        <div class="pemakaian-box">
                            <span class="pemakaian-label">Jumlah Dipakai</span>
                            <span class="pemakaian-value">
                                {{ number_format($d->stok_keluar ?? 0, 0, ',', '.') }}
                                {{ $d->nama_satuan ?? '' }}
                            </span>
                        </div>

                        <span class="cell-sub" style="margin-top:8px;">
                            Sisa stok saat ini:
                            {{ number_format($d->stok_akhirbulan ?? 0, 0, ',', '.') }}
                            {{ $d->nama_satuan ?? '' }}
                        </span>
                    </td>

                    <td class="col-pasien">
                        <div class="cell-title">
                            Pasien: {{ $d->nama_pasien ?? '-' }}
                        </div>

                        <span class="cell-sub">
                            Alamat Pasien: {{ $d->alamat_pasien ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Dokter: {{ $d->nama_dokter ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Alamat Dokter: {{ $d->alamat_dokter ?? '-' }}
                        </span>

                        <span class="cell-sub">
                            Tanggal Ambil:
                            @if (!empty($d->tgl_ambil))
                                {{ \Carbon\Carbon::parse($d->tgl_ambil)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </span>
                    </td>

                    <td class="col-aksi">
                        @if ($printId)
                            <a
                                href="{{ route('racikans.printNarkotika', $printId) }}"
                                class="btn btn-secondary btn-sm btn-cetak-narkotika"
                                target="_blank"
                            >
                                Cetak<br>Nota
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Belum ada data transaksi narkotika atau psikotropika.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="mt-4">
        {{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection