@extends('layout.conquer')
@section('title')
@section('content')
    @if ($errors->any()) untuk memunculkan error
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Ubah Stok Opname</h1>

    <form method="POST" action="{{ route('opnames.update', $datas->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="batch_id">Pilih Batch Produk</label>
                <select class="form-control" id="batchSelect" name="batch_id" required>
                @foreach ($batchs as $b)
                    @php
                        $namaProduk = $b->produks->nama ?? null;
                        $namaSatuan = $b->satuan->nama ?? '';
                        $lokasiGudang = $b->gudang->lokasi ?? '';
                    @endphp

                    @if ($namaProduk)
                        <option value="{{ $b->id }}"
                            data-stok="{{ $b->stok }}"
                            {{ $b->id == $datas->produkbatches_id ? 'selected' : '' }}>
                            {{ $namaProduk }}
                            | Batch #{{ $b->id }}
                            | Stok: {{ $b->stok }} {{ $namaSatuan }}
                            @if($lokasiGudang)
                                | Gudang: {{ $lokasiGudang }}
                            @endif
                            | Exp: {{ $b->tgl_kadaluarsa ?? '-' }}
                        </option>
                    @endif
                @endforeach
                </select>
            <small class="form-text text-muted">Pilih batch yang akan diopname.  Hanya batch dengan status tersedia yang ditampilkan.</small>
        </div>
        <div class="form-group">
            <label>Stok Sistem</label>
            <input type="number" class="form-control" id="stokSistem" name="stok_sistem" value="{{ $datas->stok_sistem }}" readonly>
            <small class="form-text text-muted">Jumlah stok yang tercatat di sistem untuk batch ini.</small>
        </div>
        <div class="form-group">
            <label for="stok_fisik">Stok Fisik</label>
            <input type="number" class="form-control" id="stokFisik" name="stok_fisik" placeholder="Masukkan Stok Fisik" value="{{ $datas->stok_fisik }}" required>
            <small class="form-text text-muted">Jumlah stok fisik sesuai perhitungan manual.</small>
        </div>
        <div class="form-group">
            <label>Selisih</label>
            <input type="number" class="form-control" id="selisih" name="selisih" value="{{ $datas->selisih }}" readonly>
            <small class="form-text text-muted">Selisih antara stok fisik dan stok sistem.</small>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal Opname</label>
            <input type="date" class="form-control" name="tanggal" value="{{ $datas->tanggal }}" required>
            <small class="form-text text-muted">Tanggal dilakukannya stok opname.</small>
        </div>
        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan', $datas->keterangan) }}</textarea>
            <small class="form-text text-muted">Isikan catatan atau alasan selisih stok bila ada.</small>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('opnames.index') }}" class="btn btn-secondary bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var batchSelect = document.getElementById('batchSelect');
            var stokSistemInput = document.getElementById('stokSistem');
            var stokFisikInput = document.getElementById('stokFisik');
            var selisihInput = document.getElementById('selisih');

            function updateStokSistem() {
                var selectedOption = batchSelect.options[batchSelect.selectedIndex];
                var stok = selectedOption.getAttribute('data-stok') || 0;
                stokSistemInput.value = stok;
                updateSelisih();
            }

            function updateSelisih() {
                var sistem = parseFloat(stokSistemInput.value) || 0;
                var fisik = parseFloat(stokFisikInput.value) || 0;
                selisihInput.value = fisik - sistem;
            }

            batchSelect.addEventListener('change', updateStokSistem);
            stokFisikInput.addEventListener('input', updateSelisih);
        });
    </script>
@endsection
