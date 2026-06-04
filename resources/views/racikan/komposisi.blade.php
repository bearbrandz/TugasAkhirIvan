@extends('layout.conquer')
@section('title')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Daftar Komposisi Racikan</h1>

    <form method="GET" action="{{ route('racikans.komposisi', ['id' => $komposisi->id]) }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ $search }}">
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    <div class="container">
        <h2>Komposisi Racikan: {{ $komposisi->nama }}</h2>
        <p>Daftar semua komposisi dari racikan ini</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach ([
            'racikans_id' => 'Id Racikan',
            'produks_id' => 'Id Produk',
            'nama_produk' => 'Nama Produk',
            'quantity' => 'Quantity',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ] as $column => $label)
                        <th>
                            <a
                                href="{{ route('racikans.komposisi', [
                                    'id' => $komposisi->id,
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
                        <td>{{ $d->racikans_id }}</td>
                        <td>{{ $d->produks_id }}</td>
                        <td>{{ $d->nama_produk }}</td>
                        <td>{{ $d->quantity }}</td>
                        <td>{{ $d->created_at }}</td>
                        <td>{{ $d->updated_at }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <form method="POST"
                                    action="{{ route('racikans.destroyKomposisi', [$d->racikans_id, $d->produks_id]) }}"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin hapus {{ $d->nama_produk }} dari racikan ini?');">Delete</button>
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

        <a href="{{ route('racikans.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
@endsection
