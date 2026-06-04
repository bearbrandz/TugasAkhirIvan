@extends('layout.conquer')
@section('title')
@section('content')

<div class="am-page-header">
    <div>
        <h1><i class="icon-action-undo" style="margin-right:8px;color:#ef4444;"></i>Form Retur Pembelian</h1>
        <p>Nota Pembelian #{{ $notabeli->id }} &mdash; {{ \Carbon\Carbon::parse($notabeli->created_at)->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('retur.create') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Cari Nota Lain</a>
</div>

@if ($errors->any())
    <div class="am-alert am-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- Info Nota --}}
<div class="am-form-card" style="margin-bottom:16px;">
    <div class="am-form-section-title">Informasi Nota Pembelian Asal</div>
    <div class="row">
        <div class="col-md-4">
            <small class="text-muted">ID Nota</small>
            <p class="mb-1"><strong>#{{ $notabeli->id }}</strong></p>
        </div>
        <div class="col-md-4">
            <small class="text-muted">Tanggal Pembelian</small>
            <p class="mb-1"><strong>{{ \Carbon\Carbon::parse($notabeli->created_at)->format('d/m/Y H:i') }}</strong></p>
        </div>
        <div class="col-md-4">
            <small class="text-muted">Dicatat oleh</small>
            <p class="mb-1"><strong>{{ $notabeli->user->nama ?? '-' }}</strong></p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('retur.store') }}">
    @csrf
    <input type="hidden" name="notabelis_id" value="{{ $notabeli->id }}">

    {{-- Alasan Retur --}}
    <div class="am-form-card" style="margin-bottom:16px;">
        <div class="am-form-section-title">Informasi Retur</div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Alasan Retur <span class="text-danger">*</span></label>
                    <select class="form-control" name="alasan" required>
                        <option value="">-- Pilih Alasan --</option>
                        <option value="rusak"       {{ old('alasan') == 'rusak'       ? 'selected' : '' }}>Rusak</option>
                        <option value="expired"     {{ old('alasan') == 'expired'     ? 'selected' : '' }}>Expired / Kadaluarsa</option>
                        <option value="salah_kirim" {{ old('alasan') == 'salah_kirim' ? 'selected' : '' }}>Salah Kirim</option>
                        <option value="lainnya"     {{ old('alasan') == 'lainnya'     ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label>Keterangan Tambahan</label>
                    <input type="text" class="form-control" name="keterangan"
                        value="{{ old('keterangan') }}" placeholder="Keterangan opsional...">
                </div>
            </div>
        </div>
    </div>

    {{-- Item Retur --}}
    <div class="am-form-card">
        <div class="am-form-section-title">Pilih Item yang Diretur</div>
        <p class="text-muted" style="font-size:13px; margin-bottom:16px;">
            Isi kolom <strong>Qty Retur</strong> dengan jumlah yang ingin dikembalikan. Biarkan 0 untuk item yang tidak diretur.
        </p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Batch ID</th>
                    <th>Distributor</th>
                    <th>Expired</th>
                    <th>Stok Saat Ini</th>
                    <th>Harga Beli/Unit</th>
                    <th style="width:130px;">Qty Retur <span class="text-danger">*</span></th>
                    <th>Subtotal Retur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $i => $item)
                    <input type="hidden" name="items[{{ $i }}][produkbatches_id]" value="{{ $item->batch->id }}">
                    <tr>
                        <td>
                            <strong>{{ $item->batch->produks->nama ?? '-' }}</strong>
                            <br><small class="text-muted">{{ $item->batch->satuan->nama ?? '-' }}</small>
                        </td>
                        <td><code>#{{ $item->batch->id }}</code></td>
                        <td>{{ $item->batch->distributor->nama ?? '-' }}</td>
                        <td>
                            @if($item->batch->tgl_kadaluarsa)
                                @php $exp = \Carbon\Carbon::parse($item->batch->tgl_kadaluarsa); @endphp
                                <span class="{{ $exp->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ $exp->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($item->batch->stok, 0, ',', '.') }}</strong>
                        </td>
                        <td>Rp {{ number_format($item->batch->unitprice, 0, ',', '.') }}</td>
                        <td>
                            <input type="number" class="form-control retur-qty"
                                name="items[{{ $i }}][quantity]"
                                value="{{ old('items.' . $i . '.quantity', 0) }}"
                                min="0" max="{{ $item->batch->stok }}"
                                data-price="{{ $item->batch->unitprice }}"
                                data-index="{{ $i }}">
                        </td>
                        <td>
                            <strong id="subtotal-{{ $i }}">Rp 0</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right"><strong>Total Nilai Retur:</strong></td>
                    <td><strong id="grand-total" style="color:#ef4444;">Rp 0</strong></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin memproses retur ini? Stok akan dikurangi dan HPP akan dihitung ulang.')">
                <i class="fa fa-send"></i> Proses Retur
            </button>
            <a href="{{ route('retur.index') }}" class="btn btn-default ml-2">Batal</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatRupiah(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function recalc() {
        var grand = 0;
        document.querySelectorAll('.retur-qty').forEach(function (input) {
            var qty     = parseFloat(input.value) || 0;
            var price   = parseFloat(input.getAttribute('data-price')) || 0;
            var idx     = input.getAttribute('data-index');
            var sub     = qty * price;
            grand      += sub;
            var el = document.getElementById('subtotal-' + idx);
            if (el) el.textContent = formatRupiah(sub);
        });
        document.getElementById('grand-total').textContent = formatRupiah(grand);
    }

    document.querySelectorAll('.retur-qty').forEach(function (input) {
        input.addEventListener('input', recalc);
    });

    recalc();
});
</script>
@endsection
