@extends('layout.conquer')

@section('title')

@section('content')
<a href="{{ url('notabelis') }}">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow hover:shadow-lg transition cursor-pointer mt-4">
        <div class="p-4 flex justify-between items-center">
            <div class="font-semibold text-black dark:text-white">
                Daftar Transaksi Pembelian
            </div>
        </div>
    </div>
</a>

<a href="{{ url('notajuals') }}">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow hover:shadow-lg transition cursor-pointer mt-4">
        <div class="p-4 flex justify-between items-center">
            <div class="font-semibold text-black dark:text-white">
                Daftar Transaksi Penjualan
            </div>
        </div>
    </div>
</a>

<a href="{{ route('produks.daftarTerima') }}">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow hover:shadow-lg transition cursor-pointer mt-4">
        <div class="p-4 flex justify-between items-center">
            <div class="font-semibold text-black dark:text-white">
                Daftar Penerimaan Batch
            </div>
        </div>
    </div>
</a>

<a href="{{ route('racikans.notaRacikan') }}">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow hover:shadow-lg transition cursor-pointer mt-4">
        <div class="p-4 flex justify-between items-center">
            <div class="font-semibold text-black dark:text-white">
                Daftar Penjualan Racikan
            </div>
        </div>
    </div>
</a>
@endsection