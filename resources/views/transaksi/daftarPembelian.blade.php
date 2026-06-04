@extends('layout.conquer')

@section('title', 'Daftar Nota Pembelian')

@section('content')
    <style>
        .nota-beli-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .nota-beli-page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #f8fafc;
        }

        .nota-beli-page-header p {
            margin: 6px 0 0;
            color: #94a3b8;
        }

        .nota-beli-filter-card {
            background: #162033;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .nota-beli-filter-row {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }

        .nota-beli-filter-row:last-child {
            border-bottom: none;
        }

        .nota-beli-search-form {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .nota-beli-search-form input {
            width: 100%;
        }

        .nota-beli-report-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nota-beli-table-box {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            background: #162033;
            overflow: hidden;
        }

        .nota-beli-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0;
            color: #f8fafc;
            font-size: 13px;
        }

        .nota-beli-table thead th {
            background: #1e2b42;
            color: #f8fafc;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-color: rgba(148, 163, 184, 0.14);
            padding: 12px 10px;
            vertical-align: middle;
        }

        .nota-beli-table tbody td {
            background: #162033;
            color: #f8fafc;
            border-color: rgba(148, 163, 184, 0.12);
            padding: 12px 10px;
            vertical-align: top;
        }

        .nota-beli-table tbody tr:nth-child(even) td {
            background: #1b2638;
        }

        .nota-beli-table th a {
            color: inherit;
            text-decoration: none;
        }

        .nota-beli-table th a:hover {
            color: #fbbf24;
        }

        .col-nota {
            width: 8%;
        }

        .col-pegawai {
            width: 14%;
        }

        .col-produk {
            width: 30%;
        }

        .col-qty {
            width: 8%;
            text-align: center;
        }

        .col-subtotal {
            width: 13%;
            white-space: nowrap;
        }

        .col-tanggal {
            width: 14%;
        }

        .col-aksi {
            width: 13%;
            text-align: center;
        }

        .nota-main-text {
            display: block;
            color: #f8fafc;
            font-weight: 800;
            line-height: 1.35;
            word-break: break-word;
        }

        .nota-sub-text {
            display: block;
            margin-top: 4px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.35;
            word-break: break-word;
        }

        .nota-product-meta {
            margin-top: 6px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.4;
            word-break: break-word;
        }

        .nota-beli-action {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
        }

        .nota-beli-action .btn-action {
            width: 105px;
            min-height: 36px;
            padding: 7px 10px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .nota-empty {
            padding: 30px;
            text-align: center;
            color: #94a3b8;
        }

        @media (max-width: 1200px) {
            .nota-beli-table-box {
                overflow-x: auto;
            }

            .nota-beli-table {
                min-width: 980px;
            }
        }

        @media (max-width: 768px) {
            .nota-beli-page-header {
                flex-direction: column;
            }

            .nota-beli-search-form {
                flex-direction: column;
            }

            .nota-beli-search-form button {
                width: 100%;
            }

            .nota-beli-report-form {
                align-items: stretch;
            }

            .nota-beli-report-form select,
            .nota-beli-report-form button {
                width: 100% !important;
            }
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="nota-beli-page-header">
        <div>
            <h1>Daftar Nota Pembelian</h1>
            <p>Daftar transaksi pembelian yang tercatat di apotek.</p>
        </div>

        <a href="{{ route('notabelis.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Tambah Nota Pembelian
        </a>
    </div>

    <div class="nota-beli-filter-card">
        <div class="nota-beli-filter-row">
            <form method="GET" action="{{ route('notabelis.index') }}" class="nota-beli-search-form">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nota, pegawai, produk, distributor, atau tanggal..."
                    value="{{ $search ?? '' }}"
                >

                <button type="submit" class="btn btn-primary">
                    Cari
                </button>
            </form>
        </div>

        <div class="nota-beli-filter-row">
            <form method="GET" action="{{ route('notabelis.report') }}" class="nota-beli-report-form">
                <label for="groupBy" class="mb-0">Lihat Laporan Pembelian:</label>

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
        </div>
    </div>

    <div class="nota-beli-table-box">
        <table class="table nota-beli-table">
            <thead>
                <tr>
                    <th class="col-nota">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'notabelis_id',
                            'sort_order' => ($sortBy ?? '') == 'notabelis_id' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Nota
                            @if (($sortBy ?? '') == 'notabelis_id')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-pegawai">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'nama_pegawai',
                            'sort_order' => ($sortBy ?? '') == 'nama_pegawai' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Pegawai
                            @if (($sortBy ?? '') == 'nama_pegawai')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-produk">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'nama_produk',
                            'sort_order' => ($sortBy ?? '') == 'nama_produk' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Produk
                            @if (($sortBy ?? '') == 'nama_produk')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-qty">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'quantity',
                            'sort_order' => ($sortBy ?? '') == 'quantity' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Qty
                            @if (($sortBy ?? '') == 'quantity')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-subtotal">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'subtotal',
                            'sort_order' => ($sortBy ?? '') == 'subtotal' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Subtotal
                            @if (($sortBy ?? '') == 'subtotal')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-tanggal">
                        <a href="{{ route('notabelis.index', [
                            'sort_by' => 'created_at',
                            'sort_order' => ($sortBy ?? '') == 'created_at' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Tanggal
                            @if (($sortBy ?? '') == 'created_at')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($datas as $d)
                    @php
                        $notaBeliId = $d->notabelis_id ?? $d->notabeli->id ?? null;
                        $tanggal = $d->created_at ?? null;
                    @endphp

                    <tr>
                        <td class="col-nota">
                            <span class="nota-main-text">
                                #{{ $notaBeliId ?? '-' }}
                            </span>
                        </td>

                        <td class="col-pegawai">
                            <span class="nota-main-text">
                                {{ $d->notabeli->user->nama ?? $d->nama_pegawai ?? '-' }}
                            </span>
                        </td>

                        <td class="col-produk">
                            <span class="nota-main-text">
                                {{ $d->nama_produk ?? '-' }}
                            </span>

                            <div class="nota-product-meta">
                                Batch: {{ $d->produkbatches->id ?? '-' }}<br>
                                Produk ID: {{ $d->produks_id ?? '-' }}<br>
                                Distributor: {{ $d->nama_distributor ?? '-' }} 
                                @if(!empty($d->distributors_id))
                                    (ID: {{ $d->distributors_id }})
                                @endif
                                <br>
                                Satuan: {{ $d->nama_satuan ?? '-' }}
                            </div>
                        </td>

                        <td class="col-qty">
                            <span class="nota-main-text">
                                {{ number_format($d->quantity ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        <td class="col-subtotal">
                            <span class="nota-main-text">
                                Rp {{ number_format($d->subtotal ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        <td class="col-tanggal">
                            <span class="nota-main-text">
                                {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y') : '-' }}
                            </span>

                            <span class="nota-sub-text">
                                {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('H:i:s') : '' }}
                            </span>
                        </td>

                        <td class="col-aksi">
                            @if ($notaBeliId)
                                <div class="nota-beli-action">
                                    <a
                                        href="{{ route('notabelis.print', $notaBeliId) }}"
                                        class="btn btn-secondary btn-sm btn-action"
                                        target="_blank"
                                    >
                                        Cetak
                                    </a>

                                    <a
                                        href="{{ route('retur.fromNota', $notaBeliId) }}"
                                        class="btn btn-warning btn-sm btn-action"
                                    >
                                        Retur
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">Nota tidak valid</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="nota-empty">
                                Belum ada data nota pembelian.
                            </div>
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