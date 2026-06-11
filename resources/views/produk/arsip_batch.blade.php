@extends('layout.conquer')
@section('title', 'Arsip Batch (Global)')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
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
                    <i class="fa fa-trash" style="margin-right:8px;color:#f59e0b;"></i>
                    Arsip Batch (Riwayat Stok Dihapus)
                </h1>
                <p>Daftar seluruh riwayat stok (batch) dari semua obat yang telah dihapus</p>
            </div>
            <div>
                <a href="{{ route('produks.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali ke Daftar Obat
                </a>
            </div>
        </div>
    </div>

    <div class="am-table-wrap">
        <div class="am-table-toolbar">
            <form method="GET" action="{{ route('produks.arsipBatchAll') }}" class="am-search-bar">
                <input type="text" name="search" placeholder="Cari nama atau kode produk..." value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                @if (!empty($search))
                    <a href="{{ route('produks.arsipBatchAll') }}" class="btn btn-default btn-sm">Reset</a>
                @endif
            </form>
            <span class="text-muted" style="font-size:13px;">{{ $datas->total() }} batch di arsip</span>
        </div>

        <div class="produk-table-box">
            <table class="table produk-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Id Batch</th>
                        <th style="width: 20%;">Nama Produk</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Distributor</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Dihapus Pada</th>
                        <th style="width: 120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $d)
                        <tr>
                            <td>#{{ $d->id }}</td>
                            <td>
                                <strong>{{ $d->produks->nama ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $d->produks->kode_produk ?? '-' }}</small>
                            </td>
                            <td>{{ $d->stok }}</td>
                            <td>{{ $d->satuan->nama ?? '-' }}</td>
                            <td>{{ number_format($d->unitprice, 0, ',', '.') }}</td>
                            <td>{{ $d->distributor->nama ?? '-' }}</td>
                            <td>{{ $d->tgl_kadaluarsa }}</td>
                            <td style="color:#ef4444;">{{ $d->deleted_at->format('d/m/Y H:i') }}</td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; gap: 6px; align-items: center; margin: 0 auto; width: 100%;">
                                    <form method="POST" action="{{ route('produks.restoreBatch', $d->id) }}" style="margin:0; width: 100%;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Kembalikan stok/batch #{{ $d->id }} ke daftar aktif?')" title="Restore Batch" style="border-radius: 6px; font-size: 12px; padding: 6px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fa fa-refresh"></i> Restore
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('produks.forceDeleteBatch', $d->id) }}" style="margin:0; width: 100%;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN: Yakin hapus PERMANEN batch #{{ $d->id }}? Data TIDAK BISA dikembalikan!')" title="Hapus Permanen" style="border-radius: 6px; font-size: 12px; padding: 6px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="fa fa-trash"></i> Permanen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="am-empty">
                                    <i class="icon-trash"></i>
                                    <p>Tidak ada riwayat batch yang dihapus.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="produk-pagination" style="margin-top: 18px; display: flex; justify-content: flex-end;">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
