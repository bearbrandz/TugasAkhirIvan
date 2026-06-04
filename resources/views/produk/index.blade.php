@extends('layout.conquer')

@section('title', 'Daftar Produk')

@section('content')
<style>
    .produk-page .am-page-header {
        margin-bottom: 18px;
    }

    .produk-table-box {
        width: 100%;
        overflow: hidden;
        border-radius: 16px;
    }

    .produk-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        font-size: 13px;
    }

    .produk-table th {
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .produk-table td {
        vertical-align: middle !important;
        word-break: break-word;
    }

    .produk-col-nama {
        width: 17%;
    }

    .produk-col-kode {
        width: 8%;
    }

    .produk-col-bentuk {
        width: 9%;
    }

    .produk-col-golongan {
        width: 10%;
    }

    .produk-col-stok {
        width: 9%;
    }

    .produk-col-hpp {
        width: 11%;
    }

    .produk-col-markup {
        width: 7%;
    }

    .produk-col-harga {
        width: 11%;
    }



    .produk-col-aksi {
        width: 11%;
        text-align: center;
    }

    .produk-table th,
    .produk-table td {
        overflow-wrap: anywhere;
    }

    .produk-table th {
        font-size: 12px;
    }

    .produk-table td {
        font-size: 13px;
    }

    .produk-action-btns {
        max-width: 100%;
    }

    .produk-price {
        white-space: normal;
    }

    .produk-name {
        display: block;
        font-weight: 800;
        color: #f8fafc;
        line-height: 1.35;
    }

    .produk-desc {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.35;
    }

    .produk-price {
        font-weight: 800;
        white-space: nowrap;
    }

    .produk-action-btns {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .produk-action-btns .btn {
        width: 34px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .produk-pagination {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
    }

    .produk-pagination nav {
        display: flex;
        align-items: center;
    }

    .produk-pagination .pagination {
        margin: 0;
        display: flex;
        gap: 5px;
        align-items: center;
        flex-wrap: wrap;
    }

    .produk-pagination .page-item {
        margin: 0 !important;
    }

    .produk-pagination .page-link {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        line-height: 1 !important;
        overflow: hidden;
    }

    .produk-pagination .page-link svg {
        width: 14px !important;
        height: 14px !important;
    }

    .produk-pagination .page-link[rel="prev"],
    .produk-pagination .page-link[rel="next"] {
        font-size: 0 !important;
    }

    .produk-pagination .page-link[rel="prev"]::after {
        content: "‹";
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
    }

    .produk-pagination .page-link[rel="next"]::after {
        content: "›";
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
    }

    .produk-pagination p {
        margin: 0;
        color: #94a3b8;
        font-size: 13px;
    }

    @media (max-width: 1200px) {
        .produk-table-box {
            overflow-x: auto;
        }

        .produk-table {
            min-width: 1050px;
        }
    }

    @media (max-width: 768px) {
        .am-table-toolbar {
            flex-direction: column;
            align-items: stretch !important;
            gap: 12px;
        }

        .am-search-bar {
            width: 100%;
        }

        .am-search-bar input {
            width: 100%;
        }

        .produk-pagination {
            justify-content: center;
        }
    }
</style>

<div class="produk-page">
    @if (session('status'))
        <div class="am-alert am-alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="am-page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div>
                <h1>
                    <i class="icon-present" style="margin-right:8px;color:#3b82f6;"></i>
                    Daftar Produk
                </h1>
                <p>Kelola data obat dan produk farmasi Apotek Medico</p>
            </div>
            <div>
                <a href="{{ route('produks.arsip') }}" class="btn btn-default" style="color: #ef4444; border-color: #fca5a5;">
                    <i class="icon-trash"></i> Lihat Arsip Produk
                </a>
            </div>
        </div>
    </div>

    <div class="am-table-wrap">
        <div class="am-table-toolbar">
            <form method="GET" action="{{ route('produks.index') }}" class="am-search-bar">
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama produk..."
                    value="{{ $search ?? '' }}"
                >

                <button type="submit" class="btn btn-primary btn-sm">
                    Cari
                </button>

                @if (!empty($search))
                    <a href="{{ route('produks.index') }}" class="btn btn-default btn-sm">
                        Reset
                    </a>
                @endif
            </form>

            <span class="text-muted" style="font-size:13px;">
                {{ $datas->total() }} produk ditemukan
            </span>
        </div>

        <div class="produk-table-box">
            <table class="table produk-table">
                <thead>
                    <tr>
                        <th class="produk-col-nama">
                            <a
                                href="{{ route('produks.index', [
                                    'sort_by' => 'nama',
                                    'sort_order' => ($sortBy ?? '') == 'nama' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                                    'search' => $search ?? '',
                                ]) }}"
                                style="color:inherit;text-decoration:none;"
                            >
                                Nama Produk
                                @if (($sortBy ?? '') == 'nama')
                                    <i class="fa fa-sort-{{ ($sortOrder ?? 'asc') == 'asc' ? 'asc' : 'desc' }}" style="font-size:10px;"></i>
                                @endif
                            </a>
                        </th>

                        <th class="produk-col-kode">Kode</th>
                        <th class="produk-col-bentuk">Bentuk</th>

                        <th class="produk-col-golongan">
                            <a
                                href="{{ route('produks.index', [
                                    'sort_by' => 'golongan',
                                    'sort_order' => ($sortBy ?? '') == 'golongan' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc',
                                    'search' => $search ?? '',
                                ]) }}"
                                style="color:inherit;text-decoration:none;"
                            >
                                Golongan
                                @if (($sortBy ?? '') == 'golongan')
                                    <i class="fa fa-sort-{{ ($sortOrder ?? 'asc') == 'asc' ? 'asc' : 'desc' }}" style="font-size:10px;"></i>
                                @endif
                            </a>
                        </th>

                        <th class="produk-col-stok">Stok</th>
                        <th class="produk-col-hpp">HPP Avg</th>
                        <th class="produk-col-markup">Markup</th>
                        <th class="produk-col-harga">Harga Jual</th>

                        <th class="produk-col-aksi">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($datas as $d)
                        <tr>
                            <td class="produk-col-nama">
                                <span class="produk-name">
                                    {{ $d->nama ?? '-' }}
                                </span>

                                @if (!empty($d->deskripsi))
                                    <span class="produk-desc">
                                        {{ \Illuminate\Support\Str::limit($d->deskripsi, 45) }}
                                    </span>
                                @endif
                            </td>

                            <td class="produk-col-kode">
                                {{ $d->kode_produk ?? '-' }}
                            </td>

                            <td class="produk-col-bentuk">
                                {{ $d->bentuk_sediaan ?: ($d->satuanJual->nama ?? '-') }}
                            </td>

                            <td class="produk-col-golongan">
                                @php
                                    $golongan = $d->golongan ?? '-';

                                    $golMap = [
                                        'bebas'        => 'am-badge-bebas',
                                        'terbatas'     => 'am-badge-terbatas',
                                        'keras'        => 'am-badge-keras',
                                        'narkotika'    => 'am-badge-narkotika',
                                        'psikotropika' => 'am-badge-psikotropika',
                                    ];

                                    $cls = $golMap[$golongan] ?? 'am-badge-bebas';
                                @endphp

                                <span class="am-badge {{ $cls }}">
                                    {{ ucfirst($golongan) }}
                                </span>
                            </td>

                            <td class="produk-col-stok">
                                @php
                                    $stok = (float) ($d->total_stok ?? 0);
                                    $threshold = (float) ($d->stok_minimum ?? 10);
                                @endphp

                                @if ($stok <= $threshold)
                                    <span class="am-badge am-badge-keras">
                                        {{ number_format($stok, 0, ',', '.') }} Kritis
                                    </span>
                                @else
                                    <strong>
                                        {{ number_format($stok, 0, ',', '.') }}
                                    </strong>
                                @endif
                            </td>

                            <td class="produk-col-hpp">
                                Rp {{ number_format($d->base_price ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="produk-col-markup">
                                {{ rtrim(rtrim(number_format($d->sellingprice ?? 0, 2, ',', '.'), '0'), ',') }}%
                            </td>

                            <td class="produk-col-harga">
                                <span class="produk-price">
                                    Rp {{ number_format((float) ($d->final_price ?? 0), 0, ',', '.') }}
                                </span>
                            </td>



                            <td class="produk-col-aksi">
                                <div class="produk-action-btns">
                                    <a
                                        href="{{ route('produks.batch', $d->id) }}"
                                        class="btn btn-info btn-sm"
                                        title="Lihat Batch"
                                    >
                                        <i class="fa fa-list"></i>
                                    </a>

                                    <a
                                        href="{{ route('produks.edit', $d->id) }}"
                                        class="btn btn-warning btn-sm"
                                        title="Edit"
                                    >
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('produks.destroy', $d->id) }}"
                                        style="display:inline;"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                            title="Hapus"
                                            onclick="return confirm('Hapus produk {{ addslashes($d->nama ?? '') }}?')"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="am-empty">
                                    <i class="icon-present"></i>
                                    <p>Belum ada produk yang terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="produk-pagination">
            {{ $datas->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection