@extends('layout.conquer')
@section('title')
@section('content')

<div class="am-page-header">
    <div>
        <h1><i class="icon-action-undo" style="margin-right:8px;color:#ef4444;"></i>Buat Retur Pembelian</h1>
        <p>Masukkan nomor nota pembelian yang akan diretur</p>
    </div>
    <a href="{{ route('retur.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
</div>

@if ($errors->any())
    <div class="am-alert am-alert-danger">
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="am-form-card">
    <div class="am-form-section-title">Langkah 1: Cari Nota Pembelian</div>
    <p class="text-muted" style="font-size:13px; margin-bottom:20px;">
        Masukkan ID nota pembelian asal. Hanya barang berstatus <strong>tersedia</strong> (sudah masuk inventory) yang dapat diretur.
    </p>
    <form method="POST" action="{{ route('retur.cari') }}">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>ID Nota Pembelian <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="notabelis_id"
                        value="{{ old('notabelis_id') }}" placeholder="Contoh: 5" required min="1">
                    <small class="text-muted">Lihat ID di halaman Nota Pembelian</small>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-search"></i> Cari Nota
        </button>
    </form>
</div>

<div class="am-alert am-alert-info" style="margin-top:0;">
    <strong>Catatan:</strong>
    Retur akan mengurangi stok batch yang bersangkutan dan memicu perhitungan ulang HPP Average secara otomatis.
</div>
@endsection
