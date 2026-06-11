@extends('layout.conquer')
@section('title', 'Arsip Batch: ' . $produk->nama)
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

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">
        <i class="fa fa-trash" style="color:#ef4444;"></i> Arsip Batch
    </h1>

    <div class="container-fluid mb-4">
        <div class="card bg-dark text-white" style="border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 12px;">
            <div class="card-body">
                <h2 class="mb-3">Informasi Produk: {{ $produk->nama }}</h2>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Kode Produk:</strong> {{ $produk->kode_produk ?? '-' }}</p>
                        <p class="mb-1"><strong>Golongan:</strong> {{ ucfirst($produk->golongan ?? '-') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Harga Jual Aktif:</strong> Rp {{ number_format((float) ($produk->final_price ?? 0), 0, ',', '.') }} <small class="text-muted">(Margin: {{ $produk->sellingprice ?? 0 }}%)</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4>Daftar Arsip Batch (Dihapus)</h4>
                <p class="mb-0 text-danger">Rincian riwayat stok/batch yang telah <b>dihapus</b> dari obat ini.</p>
            </div>
            <div>
                <a href="{{ route('produks.batch', ['id' => $produk->id]) }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali ke Daftar Aktif
                </a>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Id Batch</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th>Distributor</th>
                    <th>Tanggal Datang</th>
                    <th>Tanggal Kadaluarsa</th>
                    <th>Dihapus Pada</th>
                    <th style="width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($datas as $d)
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td>{{ $d->stok }}</td>
                        <td>{{ $d->satuan->nama ?? '-' }}</td>
                        <td>{{ $d->unitprice }}</td>
                        <td>{{ $d->distributor->nama ?? '-' }}</td>
                        <td>{{ $d->tgl_datang }}</td>
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
                        <td colspan="9" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-check-circle" style="font-size: 24px; color: #10b981; margin-bottom: 10px;"></i>
                            <p>Tidak ada riwayat batch yang dihapus.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
