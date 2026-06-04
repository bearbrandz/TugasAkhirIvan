@extends('layout.conquer')

@section('title', 'Daftar Nota Penjualan')

@section('content')
    <style>
        .nota-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .nota-page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #f8fafc;
        }

        .nota-page-header p {
            margin: 6px 0 0;
            color: #94a3b8;
        }

        .nota-filter-card {
            background: #162033;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .nota-filter-row {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }

        .nota-filter-row:last-child {
            border-bottom: none;
        }

        .nota-search-form {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .nota-search-form input {
            width: 100%;
        }

        .nota-report-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nota-table-box {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            background: #162033;
            overflow: hidden;
        }

        .nota-penjualan-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0;
            color: #f8fafc;
            font-size: 13px;
        }

        .nota-penjualan-table thead th {
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

        .nota-penjualan-table tbody td {
            background: #162033;
            color: #f8fafc;
            border-color: rgba(148, 163, 184, 0.12);
            padding: 12px 10px;
            vertical-align: top;
        }

        .nota-penjualan-table tbody tr:nth-child(even) td {
            background: #1b2638;
        }

        .nota-penjualan-table th a {
            color: inherit;
            text-decoration: none;
        }

        .nota-penjualan-table th a:hover {
            color: #fbbf24;
        }

        .col-nota {
            width: 7%;
        }

        .col-pegawai {
            width: 13%;
        }

        .col-produk {
            width: 26%;
        }

        .col-qty {
            width: 7%;
            text-align: center;
        }

        .col-total {
            width: 12%;
            white-space: nowrap;
        }

        .col-tanggal {
            width: 13%;
        }

        .col-detail {
            width: 15%;
        }

        .col-aksi {
            width: 7%;
            text-align: center;
            white-space: nowrap;
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

        .nota-detail-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nota-detail-list li {
            color: #e5e7eb;
            line-height: 1.35;
            word-break: break-word;
        }

        .nota-action-btn {
            padding: 6px 10px;
            font-size: 12px;
            line-height: 1.2;
            border-radius: 8px;
        }

        .nota-empty {
            padding: 30px;
            text-align: center;
            color: #94a3b8;
        }

        @media (max-width: 1200px) {
            .nota-table-box {
                overflow-x: auto;
            }

            .nota-penjualan-table {
                min-width: 980px;
            }
        }

        @media (max-width: 768px) {
            .nota-page-header {
                flex-direction: column;
            }

            .nota-search-form {
                flex-direction: column;
            }

            .nota-search-form button {
                width: 100%;
            }

            .nota-report-form {
                align-items: stretch;
            }

            .nota-report-form select,
            .nota-report-form button {
                width: 100% !important;
            }
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="nota-page-header">
        <div>
            <h1>Daftar Nota Penjualan</h1>
            <p>Daftar transaksi penjualan yang tercatat di apotek.</p>
        </div>

        <a href="{{ route('notajuals.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Tambah Nota Penjualan
        </a>
    </div>

    <div class="nota-filter-card">
        <div class="nota-filter-row">
            <form method="GET" action="{{ route('notajuals.index') }}" class="nota-search-form">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nota, nama pegawai, nama produk, distributor, atau tanggal..."
                    value="{{ $search ?? '' }}"
                >

                <button type="submit" class="btn btn-primary">
                    Cari
                </button>
            </form>
        </div>

        <div class="nota-filter-row">
            <form method="GET" action="{{ route('notajuals.report') }}" class="nota-report-form">
                <label for="groupBy" class="mb-0">Lihat Laporan Penjualan:</label>

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

    <div class="nota-table-box">
        <table class="table nota-penjualan-table">
            <thead>
                <tr>
                    <th class="col-nota">
                        <a href="{{ route('notajuals.index', [
                            'sort_by' => 'id',
                            'sort_order' => ($sortBy ?? '') == 'id' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Nota
                            @if (($sortBy ?? '') == 'id')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-pegawai">
                        <a href="{{ route('notajuals.index', [
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
                        <a href="{{ route('notajuals.index', [
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
                        <a href="{{ route('notajuals.index', [
                            'sort_by' => 'total_qty',
                            'sort_order' => ($sortBy ?? '') == 'total_qty' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Qty
                            @if (($sortBy ?? '') == 'total_qty')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-total">
                        <a href="{{ route('notajuals.index', [
                            'sort_by' => 'total_transaksi',
                            'sort_order' => ($sortBy ?? '') == 'total_transaksi' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                            'search' => $search ?? '',
                        ]) }}">
                            Total
                            @if (($sortBy ?? '') == 'total_transaksi')
                                {{ ($sortOrder ?? 'asc') == 'asc' ? '▲' : '▼' }}
                            @endif
                        </a>
                    </th>

                    <th class="col-tanggal">
                        <a href="{{ route('notajuals.index', [
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

                    <th class="col-detail">Detail</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($datas as $d)
                    <tr>
                        <td class="col-nota">
                            <span class="nota-main-text">#{{ $d->id }}</span>
                        </td>

                        <td class="col-pegawai">
                            <span class="nota-main-text">{{ $d->nama_pegawai ?? '-' }}</span>
                            <span class="nota-sub-text">ID: {{ $d->pegawai_id ?? '-' }}</span>
                        </td>

                        <td class="col-produk">
                            <span class="nota-main-text">{{ $d->nama_produk ?? '-' }}</span>

                            <div class="nota-product-meta">
                                Batch: {{ $d->batch_ids ?? '-' }}<br>
                                Produk ID: {{ $d->produk_ids ?? '-' }}<br>
                                Distributor: {{ $d->nama_distributor ?? '-' }}<br>
                                Satuan: {{ $d->nama_satuan ?? '-' }}
                            </div>
                        </td>

                        <td class="col-qty">
                            <span class="nota-main-text">
                                {{ number_format($d->total_qty ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        <td class="col-total">
                            <span class="nota-main-text">
                                Rp {{ number_format($d->total_transaksi ?? 0, 0, ',', '.') }}
                            </span>
                        </td>

                        <td class="col-tanggal">
                            <span class="nota-main-text">
                                {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') : '-' }}
                            </span>

                            <span class="nota-sub-text">
                                {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('H:i:s') : '' }}
                            </span>
                        </td>

                        <td class="col-detail">
                            @php
                                $detailItems = collect(explode(' | ', $d->detail_produk ?? ''))->filter();
                            @endphp

                            @if($detailItems->count() > 0)
                                <ul class="nota-detail-list">
                                    @foreach($detailItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>

                        <td class="col-aksi">
                            @if(!empty($d->id))
                                <a
                                    href="{{ route('notajuals.print', $d->id) }}"
                                    class="btn btn-secondary btn-sm nota-action-btn"
                                    target="_blank"
                                >
                                    Cetak
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="nota-empty">
                                Belum ada data nota penjualan.
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