@extends('layout.conquer')

@section('title', 'Pembayaran Racikan')

@section('content')
<style>
    .checkout-racikan-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(148, 163, 184, .22);
    }

    .checkout-racikan-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #f8fafc;
    }

    .checkout-racikan-header p {
        margin: 8px 0 0;
        color: #94a3b8;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(360px, .8fr);
        gap: 18px;
        align-items: start;
    }

    .checkout-card {
        background: #162033;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 16px;
        overflow: hidden;
        color: #f8fafc;
    }

    .checkout-card-header {
        padding: 16px 18px;
        background: #1e2b42;
        border-bottom: 1px solid rgba(148, 163, 184, .16);
    }

    .checkout-card-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
    }

    .checkout-card-body {
        padding: 18px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .info-box {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 12px;
        padding: 12px;
        background: rgba(15, 23, 42, .25);
    }

    .info-label {
        display: block;
        color: #94a3b8;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .info-value {
        display: block;
        font-weight: 800;
        color: #f8fafc;
        line-height: 1.35;
    }

    .detail-table {
        width: 100%;
        margin-bottom: 0;
        color: #f8fafc;
    }

    .detail-table th {
        background: #1e2b42;
        color: #f8fafc;
        font-size: 12px;
        text-transform: uppercase;
        border-color: rgba(148, 163, 184, .16);
        padding: 10px;
    }

    .detail-table td {
        border-color: rgba(148, 163, 184, .12);
        padding: 10px;
        vertical-align: top;
    }

    .cell-sub {
        display: block;
        color: #94a3b8;
        font-size: 12px;
        margin-top: 3px;
    }

    .payment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(148, 163, 184, .14);
    }

    .payment-row:last-child {
        border-bottom: 0;
    }

    .payment-row span:first-child {
        color: #cbd5e1;
    }

    .payment-row strong {
        font-size: 18px;
        color: #f8fafc;
    }

    .payment-row.total strong {
        color: #22c55e;
        font-size: 26px;
    }

    .payment-form-group {
        margin-top: 16px;
    }

    .payment-form-group label {
        display: block;
        font-weight: 800;
        margin-bottom: 8px;
        color: #f8fafc;
    }

    .payment-input {
        background: #0f172a !important;
        border: 1px solid rgba(148, 163, 184, .28) !important;
        color: #f8fafc !important;
        min-height: 44px;
    }

    .payment-warning {
        display: none;
        color: #f87171;
        font-size: 12px;
        margin-top: 8px;
        font-weight: 700;
    }

    .change-good { color: #22c55e !important; }
    .change-bad { color: #f87171 !important; }

    .checkout-actions {
        display: flex;
        gap: 10px;
        margin-top: 18px;
    }

    .checkout-actions .btn {
        flex: 1;
        min-height: 44px;
        font-weight: 800;
    }

    @media (max-width: 1100px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .checkout-racikan-header {
            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .checkout-actions {
            flex-direction: column;
        }
    }
</style>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="checkout-racikan-header">
    <div>
        <h1>Pembayaran Racikan</h1>
        <p>Pembayaran racikan dibuat sama seperti kasir penjualan produk: pilih metode bayar, input nominal, lalu sistem menghitung kembalian.</p>
    </div>

    <a href="{{ route('racikans.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="checkout-grid">
    <div class="checkout-card">
        <div class="checkout-card-header">
            <h2>Detail Racikan</h2>
        </div>

        <div class="checkout-card-body">
            <div class="info-grid">
                <div class="info-box">
                    <span class="info-label">Nama Racikan</span>
                    <span class="info-value">{{ $racikan->nama ?? '-' }}</span>
                </div>

                <div class="info-box">
                    <span class="info-label">Tanggal Ambil</span>
                    <span class="info-value">
                        {{ $racikan->tgl_ambil ? \Carbon\Carbon::parse($racikan->tgl_ambil)->format('d/m/Y') : '-' }}
                    </span>
                </div>

                <div class="info-box">
                    <span class="info-label">Pasien</span>
                    <span class="info-value">{{ $racikan->nama_pasien ?: '-' }}</span>
                    <span class="cell-sub">{{ $racikan->alamat_pasien ?: '-' }}</span>
                </div>

                <div class="info-box">
                    <span class="info-label">Dokter</span>
                    <span class="info-value">{{ $racikan->nama_dokter ?: '-' }}</span>
                    <span class="cell-sub">{{ $racikan->alamat_dokter ?: '-' }}</span>
                </div>

                <div class="info-box" style="grid-column: 1 / -1;">
                    <span class="info-label">Aturan Pakai</span>
                    <span class="info-value">{{ $racikan->aturan_pakai ?: '-' }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table detail-table">
                    <thead>
                        <tr>
                            <th>Bahan</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga Jual/Unit</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (($detail['ringkasan_produk'] ?? []) as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['produk_nama'] ?? '-' }}</strong>
                                    @if(!empty($item['golongan']))
                                        <span class="cell-sub">Golongan: {{ ucfirst($item['golongan']) }}</span>
                                    @endif
                                    <span class="cell-sub">Markup: {{ number_format($item['markup_persen'] ?? 0, 0, ',', '.') }}%</span>
                                </td>
                                <td class="text-end">{{ number_format($item['qty'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    @php
                                        $qtyItem = (float) ($item['qty'] ?? 0);
                                        $subtotalItem = (float) ($item['subtotal_jual'] ?? 0);
                                        $hargaPerUnit = $qtyItem > 0 ? $subtotalItem / $qtyItem : 0;
                                    @endphp
                                    Rp {{ number_format($hargaPerUnit, 0, ',', '.') }}
                                </td>
                                <td class="text-end">Rp {{ number_format($item['subtotal_jual'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="checkout-card">
        <div class="checkout-card-header">
            <h2>Kasir Racikan</h2>
        </div>

        <div class="checkout-card-body">
            <div class="payment-row">
                <span>Total Bahan</span>
                <strong>Rp {{ number_format($detail['total_bahan_jual'] ?? 0, 0, ',', '.') }}</strong>
            </div>

            <div class="payment-row">
                <span>Biaya Embalase</span>
                <strong>Rp {{ number_format($detail['biaya_embalase'] ?? 0, 0, ',', '.') }}</strong>
            </div>

            <div class="payment-row total">
                <span>Total Bayar</span>
                <strong id="totalBayarText">Rp {{ number_format($detail['total_racikan'] ?? 0, 0, ',', '.') }}</strong>
            </div>

            <form method="POST" action="{{ route('racikans.bayar', $racikan->id) }}" id="checkoutRacikanForm">
                @csrf
                <input type="hidden" name="pegawai_id" value="{{ auth()->id() }}">
                <input type="hidden" name="total_bayar" id="totalBayar" value="{{ $detail['total_racikan'] ?? 0 }}">
                <input type="hidden" name="kembalian" id="kembalian" value="0">

                <div class="payment-form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_bayar" id="metodeBayar" class="form-control payment-input" required>
                        <option value="tunai" {{ old('metode_bayar', 'tunai') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ old('metode_bayar') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <div class="payment-form-group">
                    <label>Nominal Dibayar Pembeli</label>
                    <input
                        type="number"
                        name="nominal_bayar"
                        id="nominalBayar"
                        class="form-control payment-input"
                        min="0"
                        step="1"
                        value="{{ old('nominal_bayar', $detail['total_racikan'] ?? 0) }}"
                        required
                    >
                    <small class="cell-sub">Untuk transfer, nominal otomatis mengikuti total bayar.</small>
                </div>

                <div class="payment-form-group">
                    <div class="payment-row">
                        <span>Kembalian</span>
                        <strong id="kembalianText" class="change-good">Rp 0</strong>
                    </div>
                    <div id="paymentWarning" class="payment-warning">
                        Nominal pembayaran masih kurang.
                    </div>
                </div>

                <div class="checkout-actions">
                    <a href="{{ route('racikans.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" id="btnBayarRacikan" class="btn btn-primary">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const metodeBayar = document.getElementById('metodeBayar');
    const totalBayar = document.getElementById('totalBayar');
    const nominalBayar = document.getElementById('nominalBayar');
    const kembalian = document.getElementById('kembalian');
    const kembalianText = document.getElementById('kembalianText');
    const paymentWarning = document.getElementById('paymentWarning');
    const btnBayar = document.getElementById('btnBayarRacikan');
    const checkoutForm = document.getElementById('checkoutRacikanForm');

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function hitungKembalian() {
        const total = Number(totalBayar.value || 0);
        let bayar = Number(nominalBayar.value || 0);

        if (metodeBayar.value === 'transfer') {
            bayar = total;
            nominalBayar.value = total;
            nominalBayar.readOnly = true;
        } else {
            nominalBayar.readOnly = false;
        }

        const kembali = Math.round(bayar - total);

        if (kembali < 0) {
            kembalian.value = 0;
            kembalianText.textContent = 'Kurang ' + formatRupiah(Math.abs(kembali));
            kembalianText.classList.remove('change-good');
            kembalianText.classList.add('change-bad');
            paymentWarning.style.display = 'block';
            btnBayar.disabled = true;
        } else {
            kembalian.value = kembali;
            kembalianText.textContent = formatRupiah(kembali);
            kembalianText.classList.remove('change-bad');
            kembalianText.classList.add('change-good');
            paymentWarning.style.display = 'none';
            btnBayar.disabled = false;
        }
    }

    metodeBayar.addEventListener('change', hitungKembalian);
    nominalBayar.addEventListener('input', hitungKembalian);

    checkoutForm.addEventListener('submit', function (event) {
        const total = Number(totalBayar.value || 0);
        const bayar = Number(nominalBayar.value || 0);

        if (bayar < total) {
            event.preventDefault();
            alert('Nominal pembayaran kurang dari total racikan.');
            nominalBayar.focus();
        }
    });

    hitungKembalian();
});
</script>
@endsection
