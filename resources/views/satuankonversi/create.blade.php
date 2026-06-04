@extends('layout.conquer')
@section('title')
@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Tambah Konversi Satuan</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('satuankonversi.store') }}">
    @csrf
    <div class="form-group mb-3">
        <label for="satuan_dari_id">Satuan Asal</label>
        <select class="form-control" name="satuan_besar_id" required>
            <option value="">-- Pilih Satuan Asal --</option>
            @foreach ($satuans as $s)
                <option value="{{ $s->id }}" {{ old('satuan_dari_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->nama }}
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Contoh: Tablet, Strip, Biji (satuan yang akan dikonversi)</small>
    </div>

    <div class="form-group mb-3">
        <label for="satuan_ke_id">Satuan Tujuan</label>
        <select class="form-control" name="satuan_kecil_id" required>
            <option value="">-- Pilih Satuan Tujuan --</option>
            @foreach ($satuans as $s)
                <option value="{{ $s->id }}" {{ old('satuan_ke_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->nama }}
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Contoh: Strip, Box, Lusin (satuan yang menjadi acuan akhir)</small>
    </div>

    <div class="form-group mb-3">
        <label for="nilai_konversi">Nilai Konversi</label>
        <input type="number" class="form-control" name="nilai_konversi" step="0.0001" min="0.0001"
            value="{{ old('nilai_konversi') }}" placeholder="Contoh: 10 (10 Tablet = 1 Strip)" required>
        <small class="form-text text-muted">Jumlah satuan asal yang setara dengan 1 satuan tujuan.</small>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('satuankonversi.index') }}" class="btn btn-secondary ml-2">Batal</a>
</form>
@endsection
