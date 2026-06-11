@extends('layout.conquer')
@section('title', 'Daftar Produk')
@section('content')

<div class="am-page-header">
    <div>
        <h1><i class="icon-plus" style="margin-right:8px;color:#3b82f6;"></i>Tambah Produk Baru</h1>
        <p>Isi data produk farmasi yang akan didaftarkan ke sistem</p>
    </div>
    <a href="{{ route('produks.index') }}" class="btn btn-default">
        <i class="fa fa-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="am-alert am-alert-danger">
        <strong>Terdapat kesalahan input:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="am-form-card">
    <div class="am-form-section-title">Informasi Produk</div>
    <form method="POST" action="{{ route('produks.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" value="{{ old('nama') }}"
                        placeholder="Contoh: Amoxicillin 500mg" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Golongan Produk <span class="text-danger">*</span></label>
                    <select class="form-control" name="golongan" required>
                        <option value="">-- Pilih Golongan --</option>
                        @foreach(['bebas','terbatas','keras','narkotika','psikotropika','bmhp','alkes','pkrt'] as $g)
                            <option value="{{ $g }}" {{ old('golongan') == $g ? 'selected' : '' }}>
                                @if($g == 'bmhp') BMHP
                                @elseif($g == 'alkes') Alkes
                                @elseif($g == 'pkrt') PKRT
                                @else {{ ucfirst($g) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Margin Keuntungan (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="sellingprice"
                            value="{{ old('sellingprice') }}" placeholder="Contoh: 20" min="0" max="100" step="0.01">
                        <div class="input-group-addon">%</div>
                    </div>
                    <small class="text-muted">Harga jual = HPP Avg x (1 + Margin%)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>SKU / Kode Produk</label>
                    <input type="text" class="form-control" name="kode_produk"
                        value="{{ old('kode_produk') }}" placeholder="Kode unik produk (opsional)">
                </div>
            </div>
        </div>
        <!-- Additional product attributes -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Bentuk Sediaan</label>
                    <input type="text" class="form-control" name="bentuk_sediaan" value="{{ old('bentuk_sediaan') }}" placeholder="Tablet, Sirup, Kapsul, dll">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Stok Minimum</label>
                    <input type="number" class="form-control" name="stok_minimum" value="{{ old('stok_minimum') }}" placeholder="Contoh: 20" min="0">
                    <small class="text-muted">Produk dianggap kritis bila stok ≤ nilai ini.</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi Produk</label>
            <textarea class="form-control" name="deskripsi" rows="3"
                placeholder="Masukkan deskripsi, indikasi, atau catatan produk">{{ old('deskripsi') }}</textarea>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Simpan Produk
            </button>
            <a href="{{ route('produks.index') }}" class="btn btn-default ml-2">Batal</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectGolongan = document.querySelector('select[name="golongan"]');
        const inputStokMinimum = document.querySelector('input[name="stok_minimum"]');

        if (selectGolongan && inputStokMinimum) {
            selectGolongan.addEventListener('change', function() {
                // Jangan timpa jika user sudah mengetikkan nilai sendiri
                if (inputStokMinimum.value !== '' && inputStokMinimum.value !== '0' && !inputStokMinimum.dataset.isDefault) {
                    return;
                }

                let val = this.value;
                let min = 0;
                
                if (val === 'bebas' || val === 'terbatas') {
                    min = 25;
                } else if (val === 'keras') {
                    min = 20;
                } else if (val === 'narkotika' || val === 'psikotropika' || val === 'pkrt') {
                    min = 15;
                } else if (val === 'alkes' || val === 'bmhp') {
                    min = 10;
                }

                if (min > 0) {
                    inputStokMinimum.value = min;
                    inputStokMinimum.dataset.isDefault = 'true';
                }
            });

            // Tandai jika user mengubah input secara manual
            inputStokMinimum.addEventListener('input', function() {
                this.dataset.isDefault = 'false';
            });
        }
    });
</script>
@endsection
