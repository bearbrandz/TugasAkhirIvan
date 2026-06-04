@extends('layout.conquer')
@section('title')
@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Ubah Produk</h1>

<form method="POST" action="{{route('produks.update', $datas->id)}}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="nama">Nama Produk</label>
        <input type="text" class="form-control" name="nama" aria-describedby="nameHelp"
            placeholder="Masukkan Nama Produk" value="{{ $datas->nama }}">
        <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <div class="form-group">
        <label for="kode_produk">Kode Produk (SKU)</label>
        <input type="text" class="form-control" name="kode_produk" aria-describedby="kodeHelp"
            placeholder="Masukkan Kode Produk" value="{{ old('kode_produk', $datas->kode_produk) }}">
        <small id="kodeHelp" class="form-text text-muted">Opsional, bisa dikosongkan jika tidak diperlukan.</small>
    </div>
    <div class="form-group">
        <label for="bentuk_sediaan">Bentuk Sediaan</label>
        <input type="text" class="form-control" name="bentuk_sediaan" aria-describedby="bentukHelp"
            placeholder="Tablet, Sirup, Kapsul, dll" value="{{ old('bentuk_sediaan', $datas->bentuk_sediaan) }}">
        <small id="bentukHelp" class="form-text text-muted">Contoh: Tablet, Sirup, Kapsul, Krim, dan sebagainya.</small>
    </div>
    <div class="form-group">
        <label for="sellingprice">Margin Keuntungan (%)</label>
        <input type="number" class="form-control" name="sellingprice" aria-describedby="marginHelp"
            placeholder="Masukkan persentase margin" value="{{ $datas->sellingprice }}" min="0" max="100" step="0.01">
        <small id="marginHelp" class="form-text text-muted">Persentase margin di atas HPP. Contoh: 20 artinya 20%.</small>
    </div>
    <div class="form-group">
        <label for="stok_minimum">Stok Minimum</label>
        <input type="number" class="form-control" name="stok_minimum" aria-describedby="stokHelp"
            placeholder="Contoh: 20" value="{{ old('stok_minimum', $datas->stok_minimum) }}" min="0">
        <small id="stokHelp" class="form-text text-muted">Produk dianggap kritis bila stok ≤ nilai ini.</small>
    </div>
    <div class="form-group">
        <label for="golongan">Golongan Produk</label>
        <select class="form-control" name="golongan" aria-describedby="nameHelp">
            <option value="bebas" {{ $datas->golongan == 'bebas' ? 'selected' : '' }}>Bebas</option>
            <option value="terbatas" {{ $datas->golongan == 'terbatas' ? 'selected' : '' }}>Terbatas</option>
            <option value="keras" {{ $datas->golongan == 'keras' ? 'selected' : '' }}>Keras</option>
            <option value="narkotika" {{ $datas->golongan == 'narkotika' ? 'selected' : '' }}>Narkotika</option>
            <option value="psikotropika" {{ $datas->golongan == 'psikotropika' ? 'selected' : '' }}>Psikotropika</option>
        </select>
        <small id="nameHelp" class="form-text text-muted">Mohon pilih input yang diinginkan.</small>
    </div>
    <div class="form-group">
        <label for="deskripsi">Deskripsi Produk</label>
        <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $datas->deskripsi) }}</textarea>
        <small id="nameHelp" class="form-text text-muted">Mohon isikan dengan input yang diinginkan.</small>
    </div>
    <div class="form-group">
        <label>Satuan Stok/Jual Utama</label>
        <select name="satuan_jual_id" class="form-control" required>
            <option value="">-- Pilih Satuan Stok/Jual --</option>

            @foreach ($satuans as $s)
                <option
                    value="{{ $s->id }}"
                    {{ old('satuan_jual_id', $datas->satuan_jual_id ?? '') == $s->id ? 'selected' : '' }}
                >
                    {{ $s->nama }}
                </option>
            @endforeach
        </select>

        <small class="form-text text-muted">
            Satuan ini dipakai untuk stok dan penjualan produk.
            Contoh: Panadol dijual per Strip, maka pilih Strip.
        </small>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="{{ route('produks.index') }}" class="btn btn-primary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
</form>
@endsection