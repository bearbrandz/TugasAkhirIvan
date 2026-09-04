@extends('layout.conquer')
@section('title')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (!empty($expired_batches))
        <script>
            alert("Batch kadaluarsa ditemukan:\n\n{{ $expired_batches }}");
        </script>
    @endif

    @if (!empty($sixmonthsexpired_batches))
        <script>
            alert("Batch yang akan kadaluarsa dalam 6 bulan ditemukan:\n\n{{ $sixmonthsexpired_batches }}");
        </script>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-6 border-b pb-2">
        <h1 class="text-3xl font-bold text-gray-800 m-0">Daftar Batch</h1>
        <a href="{{ route('produks.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
    <form method="GET" action="{{ route('produks.batch', ['id' => $produk->id]) }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ $search }}">
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

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
                        <p class="mb-1"><strong>Deskripsi Produk:</strong><br>
                            <span style="color: #334155;">{{ $produk->deskripsi ?: 'Tidak ada deskripsi.' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <h4>Daftar Batch</h4>
        <p>Rincian riwayat seluruh stok (batch) yang pernah masuk untuk obat ini.</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach ([
            'id' => 'Id Batch',
            'produks_id' => 'Id Produk',
            'stok' => 'Stok',
            'satuan' => 'Satuan',
            'unitprice' => 'Harga',
            'status' => 'Status',
            'distributor' => 'Distributor',
            'gudang' => 'Gudang',
            'tgl_produksi' => 'Tanggal Produksi',
            'tgl_datang' => 'Tanggal Datang',
            'tgl_kadaluarsa' => 'Tanggal Kadaluarsa',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ] as $column => $label)
                        <th>
                            <a
                                href="{{ route('produks.batch', [
                                    'id' => $produk->id,
                                    'sort_by' => $column,
                                    'sort_order' => $sortBy == $column && $sortOrder == 'asc' ? 'desc' : 'asc',
                                    'search' => $search,
                                ]) }}">
                                {{ $label }}
                                @if ($sortBy == $column)
                                    {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $d)
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td>{{ $d->produks_id }}</td>
                        <td>
                            @if($d->status == 'proses_order')
                            <span class="badge" style="background-color: #f39c12; color: white; padding: 5px 8px; font-size: 11px;">
                                <i class="fa fa-clock-o"></i> Menunggu (PO)
                            </span>
                            @else
                                {{ $d->stok }}
                            @endif
                        </td>
                        <td>{{ $d->satuan->nama }}</td>
                        <td>Rp {{ number_format($d->unitprice, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($d->status) }}</td>
                        <td>{{ $d->distributor->nama }}</td>
                        <td>{{ $d->gudang->lokasi }}</td>
                        <td>{{ $d->tgl_produksi }}</td>
                        <td>{{ $d->tgl_datang }}</td>
                        <td>{{ $d->tgl_kadaluarsa }}</td>
                        <td>{{ $d->created_at }}</td>
                        <td>{{ $d->updated_at }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                            @if($d->status != 'proses_order')
                                <a class="btn btn-sm btn-warning" href="{{ route('produks.editBatch', [$d->id]) }}">Edit</a>
                                
                                <form action="{{ route('produks.destroyBatch', [$d->id]) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus batch ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>

        <hr class="my-5">

        <h4 class="mb-3"><i class="fa fa-history text-info"></i> Kartu Stok (Mutasi Barang)</h4>
        <p>Buku besar yang mencatat seluruh riwayat pergerakan (masuk/keluar) untuk produk <strong>{{ $produk->nama }}</strong> secara kronologis dari waktu ke waktu.</p>
        
        <div class="table-responsive bg-white rounded shadow-sm p-3 mb-4">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="15%">Tanggal</th>
                        <th width="20%">Jenis Transaksi</th>
                        <th width="25%">kode Keterangan</th>
                        <th width="10%">Batch</th> 
                        <th width="12%" class="text-success text-end">Masuk (In)</th>
                        <th width="12%" class="text-danger text-end">Keluar (Out)</th>
                        <th width="16%" class="text-primary text-end">Sisa Stok (Saldo)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasi as $m)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($m->tanggal)->format('d M Y H:i') }}</td>
                            <td>
                                @if(str_contains($m->jenis, 'Pembelian'))
                                    <span class="badge bg-success"><i class="fa fa-arrow-down"></i> {{ $m->jenis }}</span>
                                @elseif(str_contains($m->jenis, 'Penjualan') || str_contains($m->jenis, 'Retur'))
                                    <span class="badge bg-danger"><i class="fa fa-arrow-up"></i> {{ $m->jenis }}</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fa fa-refresh"></i> {{ $m->jenis }}</span>
                                @endif
                            </td>
                            <td><strong>{{ $m->referensi }}</strong></td>
                            <td>
                                @if($m->batch_id)
                                <span class="badge bg-secondary">Batch #{{$m->batch_id }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-success text-end fw-bold">{{ $m->masuk > 0 ? '+'.number_format($m->masuk, 0, ',', '.') : '-' }}</td>
                            <td class="text-danger text-end fw-bold">{{ $m->keluar > 0 ? '-'.number_format($m->keluar, 0, ',', '.') : '-' }}</td>
                            <td class="text-primary text-end fw-bold" style="font-size: 15px;">{{ number_format($m->sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat pergerakan stok untuk produk ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
