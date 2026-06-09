@extends('layout.conquer')

@section('title', 'Halaman Penjualan Produk')

@section('content')
    <style>
        .sales-page-title {
            color: #f8fafc;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .sales-helper {
            color: #94a3b8;
            font-size: 14px;
        }

        .sales-product-card {
            background: #162033;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 16px;
            height: 100%;
            transition: 0.2s ease;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .sales-product-card:hover {
            transform: translateY(-2px);
            border-color: rgba(248, 113, 113, 0.45);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.22);
        }

        .sales-product-title {
            color: #f8fafc;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .sales-product-info {
            color: #e5e7eb;
            margin-bottom: 8px;
        }

        .sales-product-muted {
            color: #94a3b8;
        }

        .sales-cart-card {
            background: #162033;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 16px;
            position: sticky;
            top: 86px;
            overflow: hidden;
        }

        .sales-cart-header {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        }

        .sales-cart-header h5 {
            color: #f8fafc;
            margin: 0;
            font-weight: 800;
        }

        .sales-cart-body {
            padding: 16px 18px;
        }

        .sales-cart-item {
            padding: 12px;
            border-radius: 12px;
            background: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.14);
            margin-bottom: 10px;
        }

        .sales-cart-item strong {
            color: #f8fafc;
        }

        .sales-cart-item small {
            color: #94a3b8;
        }

        .sales-cart-footer {
            padding: 16px 18px;
            border-top: 1px solid rgba(148, 163, 184, 0.16);
        }

        .sales-total-row {
            display: flex;
            justify-content: space-between;
            color: #f8fafc;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .qty-input {
            background: #0f172a !important;
            color: #f8fafc !important;
            border-color: rgba(148, 163, 184, 0.28) !important;
        }

        .sales-payment-box {
            background: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .sales-payment-label {
            color: #e5e7eb;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .sales-payment-input {
            background: #111827 !important;
            color: #f8fafc !important;
            border-color: rgba(148, 163, 184, 0.28) !important;
        }

        .sales-change-good {
            color: #22c55e;
            font-weight: 800;
        }

        .sales-change-bad {
            color: #ef4444;
            font-weight: 800;
        }

        .sales-cash-summary {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #f8fafc;
            font-weight: 800;
            margin-bottom: 8px;
        }
    </style>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan input:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ===== NOTIFIKASI KADALUARSA & STOK KRITIS ===== --}}
        @if ($batchExpired->count() > 0)
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <strong><i class="fas fa-exclamation-triangle me-1"></i> Produk Telah Kadaluarsa!</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($batchExpired as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($batchWillExpire->count() > 0)
            <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                <strong><i class="fas fa-clock me-1"></i> Produk Akan Segera Kadaluarsa (dalam 3 bulan):</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($batchWillExpire as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($lowStockProduk->count() > 0)
            <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                <strong><i class="fas fa-box-open me-1"></i> Stok Produk Hampir Habis (kurang dari 10):</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($lowStockProduk as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- ===== END NOTIFIKASI ===== --}}

        <div class="row g-4">
            <div class="col-lg-8">
                <h1 class="sales-page-title">Halaman Penjualan Produk</h1>

                <form method="GET" action="{{ route('notajuals.create') }}" class="mb-3">
                    <div class="input-group">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            class="form-control"
                            placeholder="Cari Produk..."
                        >
                        <button class="btn btn-primary" type="submit">
                            Cari
                        </button>
                    </div>
                </form>

                <div class="mb-3">
                    <p class="mb-1 text-white">
                        <strong>Pegawai:</strong> {{ auth()->user()->nama ?? auth()->user()->name ?? 'User' }}
                    </p>
                    <p class="sales-helper mb-0">
                        Produk diurutkan berdasarkan tanggal kadaluarsa terdekat.
                    </p>
                </div>

                <div class="row g-3">
                    @forelse ($prod as $p)
                        @php
                            $tglKadaluarsa = $p->tgl_kadaluarsa ? \Carbon\Carbon::parse($p->tgl_kadaluarsa)->startOfDay() : null;
                            $today = now()->startOfDay();

                            $isExpiringSoon = $tglKadaluarsa
                                && $tglKadaluarsa->greaterThanOrEqualTo($today)
                                && $tglKadaluarsa->lessThanOrEqualTo($today->copy()->addDays(30));

                            $isRacikan = !empty($p->is_racikan);
                        @endphp

                        <div class="col-md-6 col-xl-4">
                            <div class="sales-product-card">
                                <div class="card-body">
                                    <h5 class="sales-product-title">
                                        {{ $p->nama }}

                                        @if ($isExpiringSoon)
                                            <span class="badge bg-warning text-dark ms-1">
                                                Segera Kadaluarsa
                                            </span>
                                        @endif
                                    </h5>

                                    <p class="sales-product-info">
                                        <strong>Harga Jual:</strong>
                                        Rp {{ number_format($p->sellingprice ?? 0, 0, ',', '.') }}
                                        <span class="sales-product-muted">/ {{ $p->satuan_nama ?? '-' }}</span>
                                    </p>

                                    <p class="sales-product-info">
                                        <strong>Stok Tersedia:</strong>
                                        {{ number_format($p->stok ?? 0, 0, ',', '.') }}
                                        {{ $p->satuan_nama ?? '' }}
                                    </p>

                                    @if ($p->tgl_kadaluarsa)
                                        <p class="sales-product-info">
                                            <strong>Kadaluarsa:</strong>
                                            <span class="{{ $isExpiringSoon ? 'text-warning fw-bold' : 'sales-product-muted' }}">
                                                {{ \Carbon\Carbon::parse($p->tgl_kadaluarsa)->format('d/m/Y') }}
                                            </span>
                                        </p>
                                    @endif

                                    <form method="POST" action="{{ route('notajuals.cart') }}" class="mt-3">
                                        @csrf

                                        <input type="hidden" name="id" value="{{ $p->id }}">
                                        <input type="hidden" name="nama" value="{{ $p->nama }}">
                                        <input type="hidden" name="satuan" value="{{ $p->satuan_nama ?? '' }}">
                                        <input type="hidden" name="sellingprice" value="{{ $p->sellingprice ?? 0 }}">
                                        <input type="hidden" name="stok" value="{{ $p->stok ?? 0 }}">
                                        <input type="hidden" name="tgl_kadaluarsa" value="{{ $p->tgl_kadaluarsa ?? '' }}">
                                        <input type="hidden" name="distributors_id" value="{{ $p->distributors_id ?? '' }}">
                                        <input type="hidden" name="is_racikan" value="{{ $isRacikan ? 1 : 0 }}">

                                        <div class="input-group">
                                            <input
                                                type="number"
                                                name="quantity"
                                                class="form-control qty-input"
                                                min="1"
                                                max="{{ $p->stok ?? 1 }}"
                                                value="1"
                                                required
                                            >

                                            <button class="btn btn-success" type="submit">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                Produk tidak ditemukan.
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $prod->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <div class="col-lg-4">
                @php
                    $cartTotal = collect($cart ?? [])->sum(function ($item) {
                        return ((float) ($item['sellingprice'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
                    });
                @endphp

                @if (count($cart ?? []) > 0)
                    <div class="sales-cart-card">
                        <div class="sales-cart-header">
                            <h5>Keranjang Penjualan</h5>
                        </div>

                        <div class="sales-cart-body">
                            @foreach ($cart as $key => $item)
                                <div class="sales-cart-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <strong>{{ $item['nama'] ?? '-' }}</strong>

                                            @if (!empty($item['is_racikan']))
                                                <span class="badge bg-info ms-1">Racikan</span>
                                            @endif

                                            <br>

                                            <small>
                                                {{ $item['quantity'] ?? 0 }}
                                                {{ $item['satuan'] ?? '' }}
                                                x Rp {{ number_format($item['sellingprice'] ?? 0, 0, ',', '.') }}
                                            </small>

                                            <br>

                                            <small>
                                                Subtotal:
                                                Rp {{ number_format((($item['sellingprice'] ?? 0) * ($item['quantity'] ?? 0)), 0, ',', '.') }}
                                            </small>
                                        </div>

                                        <form method="POST" action="{{ route('notajualscart.delete', ['id' => $key]) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="sales-cart-footer">
                            <form method="POST" action="{{ route('notajuals.store') }}" id="checkoutForm">
                                @csrf

                                <input type="hidden" name="pegawai_id" value="{{ auth()->id() }}">

                                @foreach ($cart as $item)
                                    <input type="hidden" name="id[]" value="{{ $item['id'] }}">
                                    <input type="hidden" name="quantity[]" value="{{ $item['quantity'] }}">
                                    <input type="hidden" name="is_racikan[]" value="{{ !empty($item['is_racikan']) ? 1 : 0 }}">
                                @endforeach

                                <div class="sales-payment-box">
                                    <div class="sales-cash-summary">
                                        <span>Total Belanja</span>
                                        <span id="totalBelanjaText">Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="total_bayar"
                                        id="totalBayar"
                                        value="{{ $cartTotal }}"
                                    >
                                </div>

                                <div class="sales-payment-box">
                                    <label class="sales-payment-label">Metode Pembayaran</label>
                                    <select
                                        name="metode_bayar"
                                        id="metodeBayar"
                                        class="form-control sales-payment-input"
                                        required
                                    >
                                        <option value="tunai" {{ old('metode_bayar', 'tunai') === 'tunai' ? 'selected' : '' }}>
                                            Tunai
                                        </option>
                                        <option value="transfer" {{ old('metode_bayar') === 'transfer' ? 'selected' : '' }}>
                                            Transfer
                                        </option>
                                    </select>
                                </div>

                                <div class="sales-payment-box">
                                    <label class="sales-payment-label">Nominal Dibayar Pembeli</label>
                                    <input
                                        type="number"
                                        name="nominal_bayar"
                                        id="nominalBayar"
                                        class="form-control sales-payment-input"
                                        min="0"
                                        step="1"
                                        value="{{ old('nominal_bayar', $cartTotal) }}"
                                        required
                                    >

                                    <small class="d-block mt-2 text-muted">
                                        Untuk transfer, nominal otomatis mengikuti total belanja.
                                    </small>
                                </div>

                                <div class="sales-payment-box">
                                    <div class="sales-cash-summary">
                                        <span>Kembalian</span>
                                        <span id="kembalianText" class="sales-change-good">Rp 0</span>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="kembalian"
                                        id="kembalian"
                                        value="{{ old('kembalian', 0) }}"
                                    >

                                    <small id="paymentWarning" class="d-block mt-2 sales-change-bad" style="display:none !important;">
                                        Nominal pembayaran masih kurang.
                                    </small>
                                </div>

                                <button class="btn btn-primary w-100" type="submit" id="btnSimpanPenjualan">
                                    Simpan Penjualan
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        Keranjang penjualan masih kosong.
                    </div>
                @endif
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
        const btnSimpan = document.getElementById('btnSimpanPenjualan');
        const checkoutForm = document.getElementById('checkoutForm');

        if (!metodeBayar || !totalBayar || !nominalBayar || !kembalian || !kembalianText) {
            return;
        }

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

            // Bulatkan kembalian: ≥0.5 naik (+1), <0.5 turun (→0)
            const kembali = Math.round(bayar - total);

            if (kembali < 0) {
                kembalian.value = 0;
                kembalianText.textContent = 'Kurang ' + formatRupiah(Math.abs(kembali));
                kembalianText.classList.remove('sales-change-good');
                kembalianText.classList.add('sales-change-bad');

                if (paymentWarning) {
                    paymentWarning.style.setProperty('display', 'block', 'important');
                }

                if (btnSimpan) {
                    btnSimpan.disabled = true;
                }
            } else {
                kembalian.value = kembali;
                kembalianText.textContent = formatRupiah(kembali);
                kembalianText.classList.remove('sales-change-bad');
                kembalianText.classList.add('sales-change-good');

                if (paymentWarning) {
                    paymentWarning.style.setProperty('display', 'none', 'important');
                }

                if (btnSimpan) {
                    btnSimpan.disabled = false;
                }
            }
        }

        metodeBayar.addEventListener('change', hitungKembalian);
        nominalBayar.addEventListener('input', hitungKembalian);

        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (event) {
                const total = Number(totalBayar.value || 0);
                const bayar = Number(nominalBayar.value || 0);

                if (bayar < total) {
                    event.preventDefault();
                    alert('Nominal pembayaran kurang dari total belanja.');
                    nominalBayar.focus();
                }
            });
        }

        hitungKembalian();
    });
</script>
@endsection