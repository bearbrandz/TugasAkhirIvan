@extends('layout.conquer')
@section('title')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Daftar Nota Penerimaan</h1>

    <div class="container">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('produks.daftarTerima') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari nota terima..."
                    value="{{ $search }}">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        
        <p>Daftar transaksi penerimaan batch yang tercatat di apotek.</p>

        <!-- Table -->
        <table class="table table-striped">
                <thead>
                    <tr>
                        @foreach ([
                            'id_terima'        => 'ID Terima',
                            'pegawai_id'       => 'ID Pegawai',
                            'nama_pegawai'     => 'Nama Pegawai',
                            'id_batch'         => 'ID Batch',
                            'nama_produk'      => 'Produk / Nota',
                            'nama_dist'        => 'Nama Distributor',
                            'nama_gudang'      => 'Lokasi Gudang',
                            'jumlah_diterima'  => 'Jumlah Diterima / Stok',
                            'tgl_datang'       => 'Tanggal Datang / Expired',
                        ] as $column => $label)
                            <th>
                                <a
                                    href="{{ route('produks.daftarTerima', [
                                        'sort_by' => $column,
                                        'sort_order' => ($sortBy ?? '') === $column && ($sortOrder ?? 'asc') === 'asc' ? 'desc' : 'asc',
                                        'search' => $search ?? '',
                                    ]) }}"
                                >
                                    {{ $label }}

                                    @if (($sortBy ?? '') === $column)
                                        {{ ($sortOrder ?? 'asc') === 'asc' ? '▲' : '▼' }}
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
                        <td>{{ $d->terima_id ?? '-' }}</td>

                        <td>{{ $d->pegawai_id ?? '-' }}</td>

                        <td>{{ $d->nama_pegawai ?? '-' }}</td>

                        <td>{{ $d->batch_id ?? $d->id_batch ?? '-' }}</td>

                        <td>
                            <strong>{{ $d->nama_produk ?? '-' }}</strong>
                            <br>
                            <small class="text-muted">
                                Nota Beli #{{ $d->notabelis_id ?? '-' }}
                            </small>
                        </td>

                        <td>{{ $d->nama_distributor ?? '-' }}</td>

                        <td>{{ $d->nama_gudang ?? '-' }}</td>

                        <td>
                            <strong>{{ number_format($d->jumlah_diterima ?? 0, 0, ',', '.') }}</strong>
                            <span class="text-muted">{{ $d->nama_satuan ?? '-' }}</span>
                            <br>
                            <small class="text-muted">
                                Stok tersisa:
                                {{ number_format($d->stok_tersisa ?? 0, 0, ',', '.') }}
                                {{ $d->nama_satuan ?? '' }}
                            </small>
                        </td>

                        <td>
                            @if (!empty($d->tgl_datang))
                                {{ \Carbon\Carbon::parse($d->tgl_datang)->format('d/m/Y') }}
                            @else
                                -
                            @endif

                            <br>

                            <small class="text-muted">
                                Exp:
                                {{ !empty($d->tgl_kadaluarsa) ? \Carbon\Carbon::parse($d->tgl_kadaluarsa)->format('d/m/Y') : '-' }}
                            </small>
                        </td>

                        <td>
                            @php
                                $batchId = $d->batch_id ?? $d->id_batch ?? null;
                            @endphp

                            @if ($batchId)
                                <a href="{{ route('produks.printTerima', $batchId) }}" class="btn btn-secondary btn-sm" target="_blank">
                                    Cetak Nota
                                </a>
                            @else
                                <span class="text-muted">Tidak ada batch</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
