@extends('layout.conquer')

@section('title', 'Daftar Nota Penjualan Racikan')

@section('content')
<style>
    .nota-racikan-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(148, 163, 184, .22);
    }

    .nota-racikan-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #f8fafc;
    }

    .nota-racikan-header p {
        margin: 8px 0 0;
        color: #94a3b8;
    }

    .nota-racikan-card {
        background: #162033;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 16px;
        overflow: hidden;
    }

    .nota-racikan-filter {
        padding: 16px;
        border-bottom: 1px solid rgba(148, 163, 184, .14);
    }

    .nota-racikan-search {
        display: flex;
        gap: 8px;
    }

    .nota-racikan-search input {
        flex: 1;
        min-width: 0;
    }

    .nota-racikan-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        color: #f8fafc;
    }

    .nota-racikan-table th,
    .nota-racikan-table td {
        vertical-align: top !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
        padding: 12px 10px !important;
        border-color: rgba(148, 163, 184, .14);
        font-size: 13px;
    }

    .nota-racikan-table th {
        background: #1e2b42;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 800;
    }

    .nota-racikan-table tbody tr:nth-child(even) td {
        background: #1b2638;
    }

    .cell-main {
        display: block;
        font-weight: 800;
        color: #f8fafc;
        line-height: 1.35;
    }

    .cell-sub {
        display: block;
        color: #94a3b8;
        font-size: 12px;
        margin-top: 3px;
        line-height: 1.35;
    }

    .payment-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(59, 130, 246, .16);
        color: #93c5fd;
    }

    .col-nota { width: 12%; }
    .col-racikan { width: 24%; }
    .col-pegawai { width: 16%; }
    .col-bayar { width: 24%; }
    .col-tanggal { width: 14%; }
    .col-aksi { width: 10%; text-align: center; }

    .sort-link {
        color: inherit;
        text-decoration: none;
    }

    .sort-link:hover {
        color: #93c5fd;
    }

    .nota-racikan-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .nota-racikan-actions .btn {
        width: 100%;
        font-size: 12px;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .nota-racikan-card {
            overflow-x: auto;
        }

        .nota-racikan-table {
            min-width: 920px;
        }
    }

    @media (max-width: 768px) {
        .nota-racikan-header,
        .nota-racikan-search {
            flex-direction: column;
        }

        .nota-racikan-header .btn,
        .nota-racikan-search button {
            width: 100%;
        }
    }
</style>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="nota-racikan-header">
    <div>
        <h1>Daftar Nota Penjualan Racikan</h1>
        <p>Daftar transaksi pembayaran racikan yang tercatat di apotek.</p>
    </div>

    <a href="{{ route('racikan') }}" class="btn btn-primary">
        Buat / Jual Racikan
    </a>
</div>

<div class="nota-racikan-card">
    <div class="nota-racikan-filter">
        <form method="GET" action="{{ route('racikans.notaRacikan') }}" class="nota-racikan-search">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cari racikan, pasien, dokter, pegawai, metode bayar..."
                value="{{ $search }}"
            >
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <table class="table nota-racikan-table">
        <thead>
            <tr>
                <th class="col-nota">
                    <a class="sort-link" href="{{ route('racikans.notaRacikan', ['sort_by' => 'notajuals_id', 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                        Nota
                        @if ($sortBy == 'notajuals_id') {{ $sortOrder == 'asc' ? '▲' : '▼' }} @endif
                    </a>
                </th>
                <th class="col-racikan">
                    <a class="sort-link" href="{{ route('racikans.notaRacikan', ['sort_by' => 'nama_racikan', 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                        Racikan
                        @if ($sortBy == 'nama_racikan') {{ $sortOrder == 'asc' ? '▲' : '▼' }} @endif
                    </a>
                </th>
                <th class="col-pegawai">
                    <a class="sort-link" href="{{ route('racikans.notaRacikan', ['sort_by' => 'nama_pegawai', 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                        Pegawai
                        @if ($sortBy == 'nama_pegawai') {{ $sortOrder == 'asc' ? '▲' : '▼' }} @endif
                    </a>
                </th>
                <th class="col-bayar">
                    <a class="sort-link" href="{{ route('racikans.notaRacikan', ['sort_by' => 'total_bayar', 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                        Pembayaran
                        @if ($sortBy == 'total_bayar') {{ $sortOrder == 'asc' ? '▲' : '▼' }} @endif
                    </a>
                </th>
                <th class="col-tanggal">
                    <a class="sort-link" href="{{ route('racikans.notaRacikan', ['sort_by' => 'tanggal_transaksi', 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                        Tanggal
                        @if ($sortBy == 'tanggal_transaksi') {{ $sortOrder == 'asc' ? '▲' : '▼' }} @endif
                    </a>
                </th>
                <th class="col-aksi">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($datas as $d)
                <tr>
                    <td class="col-nota">
                        <span class="cell-main">NJ #{{ $d->notajuals_id }}</span>
                        <span class="cell-sub">Racikan #{{ $d->racikans_id }}</span>
                    </td>

                    <td class="col-racikan">
                        <span class="cell-main">{{ $d->nama_racikan }}</span>
                        <span class="cell-sub">Pasien: {{ $d->nama_pasien ?? '-' }}</span>
                        <span class="cell-sub">Dokter: {{ $d->nama_dokter ?? '-' }}</span>
                    </td>

                    <td class="col-pegawai">
                        <span class="cell-main">{{ $d->nama_pegawai }}</span>
                        <span class="cell-sub">Qty Racikan: {{ number_format($d->quantity ?? 1, 0, ',', '.') }}</span>
                    </td>

                    <td class="col-bayar">
                        <span class="payment-badge">{{ ucfirst($d->metode_bayar ?? 'tunai') }}</span>
                        <span class="cell-sub">Total: Rp {{ number_format($d->total_bayar ?? 0, 0, ',', '.') }}</span>
                        <span class="cell-sub">Dibayar: Rp {{ number_format($d->nominal_bayar ?? 0, 0, ',', '.') }}</span>
                        <span class="cell-sub">Kembalian: Rp {{ number_format($d->kembalian ?? 0, 0, ',', '.') }}</span>
                    </td>

                    <td class="col-tanggal">
                        <span class="cell-main">
                            {{ !empty($d->tanggal_transaksi) ? \Carbon\Carbon::parse($d->tanggal_transaksi)->format('d/m/Y') : '-' }}
                        </span>
                        <span class="cell-sub">
                            {{ !empty($d->tanggal_transaksi) ? \Carbon\Carbon::parse($d->tanggal_transaksi)->format('H:i') : '-' }}
                        </span>
                    </td>

                    <td class="col-aksi">
                        <div class="nota-racikan-actions">
                            <a href="{{ route('notajuals.print', $d->notajuals_id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                Cetak Nota
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Belum ada data penjualan racikan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection
