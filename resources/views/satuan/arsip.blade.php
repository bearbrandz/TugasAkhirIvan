@extends('layout.conquer')

@section('title', 'Arsip Satuan Kategori')

@section('content')
<style>
    .produk-page .am-page-header { margin-bottom: 18px; }
    .produk-table-box { width: 100%; overflow: hidden; border-radius: 16px; }
    .produk-table { width: 100%; table-layout: fixed; margin-bottom: 0; font-size: 13px; }
    .produk-table th { white-space: nowrap; vertical-align: middle !important; font-size: 12px; }
    .produk-table td { vertical-align: middle !important; word-break: break-word; font-size: 13px; }
    .produk-name { display: block; font-weight: 800; color: #f8fafc; line-height: 1.35; }
    .produk-desc { display: block; margin-top: 4px; color: #94a3b8; font-size: 12px; line-height: 1.35; }
    .produk-pagination { margin-top: 18px; display: flex; justify-content: flex-end; }
</style>

<div class="produk-page">
    @if (session('status'))
        <div class="am-alert am-alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="am-page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div>
                <h1>
                    <i class="icon-trash" style="margin-right:8px;color:#ef4444;"></i>
                    Arsip Satuan Kategori (Dihapus)
                </h1>
                <p>Daftar satuan/kategori produk yang telah dihapus dari sistem</p>
            </div>
            <div>
                <a href="{{ route('satuans.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali ke Daftar Aktif
                </a>
            </div>
        </div>
    </div>

    <div class="am-table-wrap">
        <div class="am-table-toolbar">
            <form method="GET" action="{{ route('satuans.arsip') }}" class="am-search-bar">
                <input type="text" name="search" placeholder="Cari nama satuan..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                @if (!empty($search))
                    <a href="{{ route('satuans.arsip') }}" class="btn btn-default btn-sm">Reset</a>
                @endif
            </form>
            <span class="text-muted" style="font-size:13px;">{{ $datas->total() }} satuan di arsip</span>
        </div>

        <div class="produk-table-box">
            <table class="table produk-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Nama Satuan / Kategori</th>
                        <th style="width: 30%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $d)
                        <tr>
                            <td>
                                <span class="produk-name">{{ $d->nama ?? '-' }}</span>
                                <span class="produk-desc" style="color:#ef4444; margin-top:8px;">Dihapus pada: {{ $d->deleted_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td style="text-align: center;">
                                <form method="POST" action="{{ route('satuans.restore', $d->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Kembalikan satuan {{ addslashes($d->nama ?? '') }} ke daftar aktif?')" title="Restore Satuan" style="border-radius: 6px; font-size: 12px; padding: 5px 10px;">
                                        <i class="fa fa-refresh"></i> Restore
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('satuans.force-delete', $d->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN: Anda yakin ingin menghapus PERMANEN satuan {{ addslashes($d->nama ?? '') }}? Data yang dihapus permanen TIDAK BISA dikembalikan lagi!')" title="Hapus Permanen" style="border-radius: 6px; font-size: 12px; padding: 5px 10px;">
                                        <i class="fa fa-trash"></i> Hapus Permanen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">
                                <div class="am-empty">
                                    <i class="icon-trash"></i>
                                    <p>Tidak ada satuan/kategori di dalam arsip / tempat sampah.</p>
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
