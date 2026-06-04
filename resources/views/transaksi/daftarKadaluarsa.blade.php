@extends('layout.conquer')
@section('title')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Daftar Batch Kadaluarsa</h1>

    <div class="container">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('produks.daftarKadaluarsa') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari..."
                    value="{{ $search }}">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        
        <form method="GET" action="{{ route('produks.daftarKadaluarsa') }}" class="d-flex align-items-center gap-2 mb-3">
            @if($search)
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <label for="filterKadaluarsa" class="mb-0">Tampilkan kadaluarsa:</label>
            <select name="filter" id="filterKadaluarsa" class="form-select w-auto d-inline" onchange="this.form.submit()">
                <option value="month"   {{ $filter == 'month'   ? 'selected' : '' }}>Bulan Ini</option>
                <option value="3month"  {{ $filter == '3month'  ? 'selected' : '' }}>3 Bulan ke Depan</option>
                <option value="6month"  {{ $filter == '6month'  ? 'selected' : '' }}>6 Bulan ke Depan</option>
                <option value="year"    {{ $filter == 'year'    ? 'selected' : '' }}>Tahun Ini</option>
                <option value="expired" {{ $filter == 'expired' ? 'selected' : '' }}>Sudah Kadaluarsa</option>
            </select>
            <a href="{{ route('produks.reportKadaluarsa', ['filter' => $filter]) }}" class="btn btn-outline-primary btn-sm">Lihat Laporan</a>
        </form>

        <p>Daftar batch kadaluarsa yang tercatat di apotek.</p>

        <!-- Table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    @foreach ([
            'batch_id' => 'Batch ID',
            'nama_produk' => 'Nama Produk',
            'stok' => 'Stok Produk',
            'nama_satuan' => 'Satuan Batch',
            'hpp' => 'HPP Produk',
            'total_harga' => 'Total Harga',
            'tgl_kadaluarsa' => 'Tanggal Kadaluarsa'
        ] as $column => $label)
                        <th>
                            <a
                                href="{{ route('produks.daftarKadaluarsa', ['sort_by' => $column, 'sort_order' => $sortOrder == 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                                {{ $label }}
                                @if ($sortBy == $column)
                                    {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                    @endforeach
                    {{-- <th>Aksi</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $d)
                    <tr>
                        <td>{{ $d->batch_id ?? '-' }}</td>
                        <td>{{ $d->nama_produk ?? '-' }}</td>
                        <td>{{ $d->stok ?? '-' }}</td>
                        <td>{{ $d->nama_satuan ?? '-' }}</td>
                        <td>
                            Rp {{ number_format($d->hpp_produk ?? $d->unitprice ?? 0, 0, ',', '.') }}
                        </td>
                        <td>RP {{ number_format($d->total_harga ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $d->tgl_kadaluarsa }}</td>
                        {{-- <td> --}}
                            {{-- <a class="btn btn-warning" href="{{ route('notabelis.edit', $d->id) }}">Edit</a> --}}
                            {{-- <a href="{{ route('notabelis.print', $d->notabeli->id) }}" class="btn btn-secondary btn-sm"
                                target="_blank">
                                Cetak Nota
                            </a> --}}
                            {{-- <form method="POST" action="{{ route('notabelis.destroy', $d->notabelis_id) }}"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="Delete" class="btn btn-danger"
                                    onclick="return confirm('Are you sure to delete Nota {{ $d->notabelis_id }}?');">
                            </form> --}}
                        {{-- </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
