@extends('layout.conquer')

@section('title', 'User Dashboard')

@section('content')
    <div class="flex justify-between items-center px-6 py-4 bg-white shadow">
        <h1 class="text-xl font-semibold text-black">Halaman Utama</h1>

        <form method="GET" action="{{ url('/') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Produk..."
                class="form-input px-4 py-2 rounded border border-gray-300" />
        </form>
    </div>

    <div class="p-6 bg-gray-50 overflow-y-auto">
        <form method="GET" action="{{ url('/') }}" class="flex flex-wrap gap-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <select name="sort_by" class="form-select px-4 py-2 rounded border border-gray-300">
                    <option value="nama" {{ request('sort_by') === 'nama' ? 'selected' : '' }}>Nama</option>
                    <option value="total_stok" {{ request('sort_by') === 'total_stok' ? 'selected' : '' }}>Stok</option>
                    <option value="sellingprice" {{ request('sort_by') === 'sellingprice' ? 'selected' : '' }}>Harga
                    </option>
                </select>

                <select name="sort_order" class="form-select px-4 py-2 rounded border border-gray-300">
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Sort</button>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($datas as $d)
                @php
                    $batch = $d->produkbatches
                        ->filter(function ($batch) {
                            return $batch->status === 'tersedia' &&
                                (is_null($batch->tgl_kadaluarsa) || $batch->tgl_kadaluarsa > now());
                        })
                        ->sortByDesc('created_at')
                        ->first();
                @endphp
                <a href="{{ route('produks.show', $d->id) }}" class="block">
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition hover:ring-2 hover:ring-blue-400">
                        <div class="flex justify-center pt-4">
                            <img height="100px" src="{{ asset('/produk_image/' . $d->image) }}"
                                class="w-48 h-48 object-cover">
                        </div>
                        <div class="p-4 flex justify-between items-center">
                            <div class="font-semibold">
                                Nama Produk: <br> {{ $d->nama }}
                            </div>
                            <div class="text-sm text-right text-gray-600">
                                @if ($batch)
                                    <p>Stok: {{ $d->total_stok ?? 0 }}</p>
                                    <p>Harga: Rp{{ number_format($d->final_price ?? 0, 0, ',', '.') }}</p>
                                @else
                                    <p class="italic text-red-500">Belum ada batch</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        {{-- Pagination --}}
        <div class="mt-4">
            {{ $datas->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
