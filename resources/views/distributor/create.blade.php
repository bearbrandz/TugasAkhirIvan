@extends('layout.conquer')
@section('title')
@section('content')

<div class="am-page-header">
    <div>
        <h1><i class="icon-plus" style="margin-right:8px;color:#3b82f6;"></i>Tambah Distributor</h1>
        <p>Daftarkan pemasok atau distributor baru</p>
    </div>
    <a href="{{ route('distributors.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
</div>

@if ($errors->any())
    <div class="am-alert am-alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="am-form-card">
    <div class="am-form-section-title">Data Distributor</div>
    <form method="POST" action="{{ route('distributors.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Distributor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" value="{{ old('nama') }}"
                        placeholder="Nama perusahaan distributor" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" class="form-control" name="no_hp" value="{{ old('no_hp') }}"
                        placeholder="Contoh: 021-12345678">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <textarea class="form-control" name="alamat" rows="3" placeholder="Alamat lengkap distributor">{{ old('alamat') }}</textarea>
        </div>
        <div style="margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
            <a href="{{ route('distributors.index') }}" class="btn btn-default ml-2">Batal</a>
        </div>
    </form>
</div>
@endsection
