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

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Daftar Batch</h1>

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
                            <span style="color: #cbd5e1;">{{ $produk->deskripsi ?: 'Tidak ada deskripsi.' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4>Daftar Batch</h4>
                <p class="mb-0">Rincian riwayat seluruh stok (batch) yang pernah masuk untuk obat ini.</p>
            </div>
            <div>
                <a href="{{ route('produks.arsipBatch', ['id' => $produk->id]) }}" class="btn btn-default" style="color: #ef4444; border-color: #fca5a5;">
                    <i class="fa fa-trash"></i> Lihat Arsip Batch
                </a>
            </div>
        </div>

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
                        <td>{{ $d->stok }}</td>
                        <td>{{ $d->satuan->nama }}</td>
                        <td>{{ $d->unitprice }}</td>
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
                                <a class="btn btn-sm btn-warning" href="{{ route('produks.editBatch', [$d->id]) }}">Edit</a>

                                <form method="POST" action="{{ route('produks.destroyBatch', [$d->id]) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin hapus batch #{{ $d->id }}?');">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>

        <a href="{{ route('produks.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
@endsection
