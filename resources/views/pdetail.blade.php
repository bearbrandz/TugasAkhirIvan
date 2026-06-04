@extends('layout.conquer')

@section('title', 'Detail Produk')

@section('content')
<div class="max-w-5xl mx-auto mt-8 p-6 bg-white rounded-2xl shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Left: Image --}}
        <div class="flex justify-center items-start">
            <img src="{{ asset('/produk_image/' . $produk->image) }}" alt="{{ $produk->nama }}"
                 class="w-full max-w-sm rounded-lg shadow">
        </div>

        {{-- Right: Product Info --}}
        <div class="flex flex-col justify-between space-y-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $produk->nama }}</h1>
                <p class="text-gray-600 text-sm mb-4">{{ $produk->deskripsi }}</p>

                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                    Golongan: {{ $produk->golongan }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="text-gray-700">
                    <p class="font-semibold">Satuan:</p>
                    <p>{{ $satuan }}</p>
                </div>
                <div class="text-gray-700">
                    <p class="font-semibold">Stok Tersedia:</p>
                    <p>{{ $stok }} unit</p>
                </div>
                <div class="col-span-2">
                    <p class="text-lg font-semibold text-green-600">
                        Harga Jual: Rp{{ number_format($produk->final_price ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
