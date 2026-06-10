@extends('layout.conquer')

@section('title', 'Daftar Racikan')

@section('content')
<style>
    .racikan-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .racikan-page-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #f8fafc;
    }

    .racikan-page-header p {
        margin: 8px 0 0;
        color: #94a3b8;
    }

    .racikan-filter-card {
        background: #162033;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 18px;
    }

    .racikan-search-form {
        display: flex;
        gap: 8px;
    }

    .racikan-search-form input {
        width: 100%;
    }

    .racikan-table-box {
        width: 100%;
        background: #162033;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        overflow: hidden;
    }

    .racikan-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        color: #f8fafc;
        font-size: 13px;
    }

    .racikan-table thead th {
        background: #1e2b42;
        color: #f8fafc;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        border-color: rgba(148, 163, 184, 0.14);
        padding: 12px 10px;
        vertical-align: middle;
    }

    .racikan-table tbody td {
        background: #162033;
        color: #f8fafc;
        border-color: rgba(148, 163, 184, 0.12);
        padding: 12px 10px;
        vertical-align: top;
    }

    .racikan-table tbody tr:nth-child(even) td {
        background: #1b2638;
    }

    .cell-main {
        display: block;
        color: #f8fafc;
        font-weight: 800;
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

    .racikan-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        margin-top: 6px;
    }

    .badge-resep-ada {
        background: rgba(34, 197, 94, 0.16);
        color: #22c55e;
    }

    .badge-resep-kosong {
        background: rgba(234, 179, 8, 0.16);
        color: #facc15;
    }

    .badge-resep-wajib {
        background: rgba(239, 68, 68, 0.16);
        color: #f87171;
    }

    .komposisi-list {
        margin: 0;
        padding-left: 16px;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.55;
    }

    .komposisi-list li {
        margin-bottom: 3px;
    }

    .racikan-action {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: stretch;
    }

    .racikan-action .btn,
    .racikan-action a.btn {
        width: 100%;
        min-height: 32px;
        padding: 7px 9px;
        font-size: 12px;
        font-weight: 800;
        border-radius: 6px;
        text-align: center;
    }

    .racikan-empty {
        padding: 28px;
        text-align: center;
        color: #94a3b8;
    }

    .col-id { width: 6%; text-align:center; }
    .col-racikan { width: 20%; }
    .col-pasien { width: 19%; }
    .col-aturan { width: 18%; }
    .col-komposisi { width: 20%; }
    .col-resep { width: 10%; }
    .col-aksi { width: 10%; }

    .sort-link {
        color: inherit;
        text-decoration: none;
    }

    .sort-link:hover {
        color: #93c5fd;
        text-decoration: none;
    }

    @media (max-width: 1200px) {
        .racikan-table-box {
            overflow-x: auto;
        }

        .racikan-table {
            min-width: 1050px;
        }
    }

    @media (max-width: 768px) {
        .racikan-page-header {
            flex-direction: column;
        }

        .racikan-page-header .btn {
            width: 100%;
        }

        .racikan-search-form {
            flex-direction: column;
        }

        .racikan-search-form button,
        .racikan-search-form a {
            width: 100%;
        }
    }
</style>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="racikan-page-header">
    <div>
        <h1>Daftar Racikan</h1>
        <p>Kelola racikan, komposisi bahan, bukti resep, dan proses penjualan racikan.</p>
    </div>

    <a href="{{ route('racikans.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Buat Racikan Baru
    </a>
</div>

<div class="racikan-filter-card">
    <form method="GET" action="{{ route('racikans.index') }}" class="racikan-search-form">
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Cari nama racikan, pasien, dokter, aturan pakai..."
            value="{{ request('search') }}"
        >

        <button type="submit" class="btn btn-primary">
            Cari
        </button>

        @if(request('search'))
            <a href="{{ route('racikans.index') }}" class="btn btn-default">
                Reset
            </a>
        @endif
    </form>
</div>

<div class="racikan-table-box">
    <table class="table racikan-table">
        <thead>
            <tr>
                <th class="col-id">
                    <a class="sort-link" href="{{ route('racikans.index', [
                        'sort_by' => 'id',
                        'sort_order' => ($sortBy ?? 'id') === 'id' && ($sortOrder ?? 'desc') === 'desc' ? 'asc' : 'desc',
                        'search' => request('search')
                    ]) }}">
                        ID
                        @if (($sortBy ?? 'id') === 'id')
                            {{ ($sortOrder ?? 'desc') === 'desc' ? '▼' : '▲' }}
                        @endif
                    </a>
                </th>

                <th class="col-racikan">
                    <a class="sort-link" href="{{ route('racikans.index', [
                        'sort_by' => 'nama',
                        'sort_order' => ($sortBy ?? '') === 'nama' && ($sortOrder ?? 'desc') === 'desc' ? 'asc' : 'desc',
                        'search' => request('search')
                    ]) }}">
                        Racikan
                        @if (($sortBy ?? '') === 'nama')
                            {{ ($sortOrder ?? 'desc') === 'desc' ? '▼' : '▲' }}
                        @endif
                    </a>
                </th>

                <th class="col-pasien">Pasien / Dokter</th>
                <th class="col-aturan">Aturan / Biaya</th>
                <th class="col-komposisi">Komposisi</th>
                <th class="col-resep">Resep</th>
                <th class="col-aksi">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($datas as $d)
                @php
                    $komposisi = $d->racikanproduks ?? collect();

                    $butuhResep = $komposisi->contains(function ($item) {
                        $golongan = strtolower($item->produk->golongan ?? '');
                        return in_array($golongan, ['keras', 'narkotika', 'psikotropika']);
                    });

                    $isSusulan = str_contains($d->nama ?? '', 'Resep Susulan');
                    $adaResep = !empty($d->bukti_resep);
                    $sudahTerjual = $d->notajualracikans->count() > 0;
                    $bolehJual = (!$butuhResep || $adaResep || $isSusulan) && !$sudahTerjual;
                @endphp

                <tr>
                    <td class="col-id text-center">
                        <span class="cell-main">#{{ $d->id }}</span>
                    </td>

                    <td class="col-racikan">
                        <span class="cell-main">{{ $d->nama ?? '-' }}</span>

                        @if(!empty($d->deskripsi))
                            <span class="cell-sub">
                                {{ \Illuminate\Support\Str::limit($d->deskripsi, 90) }}
                            </span>
                        @endif

                        <span class="cell-sub">
                            Tgl Ambil:
                            {{ $d->tgl_ambil ? \Carbon\Carbon::parse($d->tgl_ambil)->format('d/m/Y') : '-' }}
                        </span>
                    </td>

                    <td class="col-pasien">
                        <span class="cell-main">
                            Pasien: {{ $d->nama_pasien ?: '-' }}
                        </span>
                        <span class="cell-sub">
                            Dokter: {{ $d->nama_dokter ?: '-' }}
                        </span>

                        @if(!empty($d->alamat_pasien))
                            <span class="cell-sub">
                                Alamat pasien: {{ \Illuminate\Support\Str::limit($d->alamat_pasien, 70) }}
                            </span>
                        @endif
                    </td>

                    <td class="col-aturan">
                        @php
                            $biayaEmbalase = (float) ($d->biaya_embalase ?? 0);
                            $estimasiBahan = 0;

                            foreach (($d->racikanproduks ?? collect()) as $komponen) {
                                $produk = $komponen->produk ?? null;

                                if (!$produk) {
                                    continue;
                                }

                                $qtyKomposisi = (float) ($komponen->quantity ?? 0);
                                $markupPersen = (float) ($produk->sellingprice ?? 0);

                                $batchAktif = \DB::table('produkbatches')
                                    ->where('produks_id', $produk->id)
                                    ->where('stok', '>', 0)
                                    ->where('status', 'tersedia')
                                    ->where(function ($q) {
                                        $q->whereDate('tgl_kadaluarsa', '>', now())
                                            ->orWhereNull('tgl_kadaluarsa');
                                    })
                                    ->orderByRaw('tgl_kadaluarsa IS NULL, tgl_kadaluarsa ASC')
                                    ->orderBy('id', 'asc')
                                    ->first();

                                $hppBahan = 0;

                                if ($batchAktif) {
                                    $hppBahan = (float) (($batchAktif->hpp_avg_per_unit ?? 0) ?: ($batchAktif->unitprice ?? 0));
                                }

                                $hargaJualBahan = $hppBahan + ($hppBahan * $markupPersen / 100);
                                $estimasiBahan += $qtyKomposisi * $hargaJualBahan;
                            }

                            $estimasiTotal = $estimasiBahan + $biayaEmbalase;
                        @endphp

                        <span class="cell-main">
                            Estimasi Total: Rp {{ number_format($estimasiTotal, 0, ',', '.') }}
                        </span>

                        <span class="cell-sub">
                            Bahan: Rp {{ number_format($estimasiBahan, 0, ',', '.') }}
                        </span>

                        <span class="cell-sub">
                            Embalase: Rp {{ number_format($biayaEmbalase, 0, ',', '.') }}
                        </span>

                        <span class="cell-sub">
                            Aturan: {{ \Illuminate\Support\Str::limit($d->aturan_pakai ?? '-', 80) }}
                        </span>
                    </td>

                    <td class="col-komposisi">
                        @if($komposisi->count() > 0)
                            <ul class="komposisi-list">
                                @foreach($komposisi->take(4) as $item)
                                    <li>
                                        {{ $item->produk->nama ?? '-' }}
                                        x {{ number_format($item->quantity ?? 0, 0, ',', '.') }}

                                        @if(!empty($item->produk->golongan))
                                            <span class="cell-sub" style="display:inline;">
                                                ({{ ucfirst($item->produk->golongan) }})
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if($komposisi->count() > 4)
                                <span class="cell-sub">
                                    +{{ $komposisi->count() - 4 }} produk lainnya
                                </span>
                            @endif
                        @else
                            <span class="cell-sub">Belum ada komposisi</span>
                        @endif
                    </td>

                    <td class="col-resep">
                        @if($adaResep)
                            <a href="{{ asset('storage/' . $d->bukti_resep) }}" target="_blank" class="btn btn-info btn-sm">
                                Lihat
                            </a>

                            <span class="racikan-badge badge-resep-ada">
                                Ada
                            </span>
                        @elseif($isSusulan)
                            <span class="racikan-badge" style="background: rgba(148, 163, 184, 0.16); color: #cbd5e1;">
                                Auto (SIPNAP)
                            </span>
                            <span class="cell-sub">Tanpa Foto</span>
                        @else
                            @if($butuhResep)
                                <span class="racikan-badge badge-resep-wajib">
                                    Wajib
                                </span>
                                @if(!$sudahTerjual)
                                    <span class="cell-sub">
                                        Upload lewat Edit
                                    </span>
                                @endif
                            @else
                                <span class="racikan-badge badge-resep-kosong">
                                    Opsional
                                </span>
                            @endif
                        @endif
                    </td>

                    <td class="col-aksi">
                        <div class="racikan-action">
                            @if($sudahTerjual)
                                <span class="racikan-badge badge-resep-ada text-center mb-1" style="width: 100%; display:block;">
                                    <i class="fa fa-check-circle"></i> Terjual
                                </span>
                            @else
                                @if($bolehJual)
                                    <a
                                        href="{{ route('racikans.checkout', $d->id) }}"
                                        class="btn btn-success btn-sm"
                                    >
                                        Bayar / Jual
                                    </a>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        Bayar / Jual
                                    </button>
                                @endif
                            @endif

                            <a class="btn btn-info btn-sm" href="{{ route('racikans.komposisi', $d->id) }}">
                                Komposisi
                            </a>

                            @if(!$sudahTerjual)
                                <a class="btn btn-warning btn-sm" href="{{ route('racikans.edit', $d->id) }}">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('racikans.destroy', $d->id) }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus racikan {{ addslashes($d->nama) }}?')"
                                    >
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="racikan-empty">
                            Belum ada data racikan.
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