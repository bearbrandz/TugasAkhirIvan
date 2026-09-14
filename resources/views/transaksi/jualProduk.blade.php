@extends('layout.conquer')

@section('title', 'Halaman Penjualan Produk')

@section('content')
    <style>
        .sales-page-title {
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .sales-helper {
            color: #64748b;
            font-size: 14px;
        }

        .sales-product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            height: 100%;
            transition: 0.2s ease;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .sales-product-card:hover {
            transform: translateY(-2px);
            border-color: rgba(232, 25, 44, 0.4);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        }

        .sales-product-title {
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .sales-product-info {
            color: #334155;
            margin-bottom: 8px;
        }

        .sales-product-muted {
            color: #64748b;
        }

        .sales-cart-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            position: sticky;
            top: 86px;
            overflow: hidden;
        }

        .sales-cart-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .sales-cart-header h5 {
            color: #1e293b;
            margin: 0;
            font-weight: 800;
        }

        .sales-cart-body {
            padding: 16px 18px;
        }

        .sales-cart-item {
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }

        .sales-cart-item strong {
            color: #1e293b;
        }

        .sales-cart-item small {
            color: #64748b;
        }

        .sales-cart-footer {
            padding: 16px 18px;
            border-top: 1px solid #e2e8f0;
        }

        .sales-total-row {
            display: flex;
            justify-content: space-between;
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .qty-input {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }

        .sales-payment-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .sales-payment-label {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .sales-payment-input {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
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
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 8px;
        }
    </style>

    <div class="container-fluid">

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
                                        {{ strtoupper($p->nama) }}

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
                                            @if(isset($item['golongan']) && in_array(strtolower($item['golongan']), ['keras']))
                                                <div class="mt-1">
                                                    <span class="badge bg-danger text-white shadow-sm" style="font-size: 0.7rem; padding: 5px 8px;">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> OBAT KERAS - Perhatikan Resep/OWA
                                                    </span>
                                                </div>
                                            @endif


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
                            <form method="POST" action="{{ route('notajuals.store') }}" id="checkoutForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="pegawai_id" value="{{ auth()->id() }}">

                                @foreach ($cart as $item)
                                    <input type="hidden" name="id[]" value="{{ $item['id'] }}">
                                    <input type="hidden" name="quantity[]" value="{{ $item['quantity'] }}">
                                    <input type="hidden" name="is_racikan[]" value="{{ !empty($item['is_racikan']) ? 1 : 0 }}">
                                @endforeach
                                <div class="sales-payment-box">
                                    <label class="sales-payment-label d-flex justify-content-between align-items-center">
                                        <span>Dokter (Opsional, Wajib untuk Resep)</span> 
                                    <button type="button" class="btn btn-sm btn-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAddDokter">+ Baru</button>
                                </label>
                                    <select name="dokter_id" id="dokter_id" class="form-control sales-payment-input">
                                        <option value="">-- Pilih Dokter --</option>
                                        @foreach($dokters as $d)
                                        <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sales-payment-box mt-3">
                                    <label class="sales-payment-label d-flex justify-content-between align-items-center">
                                        <span>Pasien (Opsional, Wajib untuk Resep)</span>
                                        <button type="button" class="btn btn-sm btn-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAddPasien">+ Baru</button>
                                    </label>
                                    <select name="pasien_id" id="pasien_id" class="form-control sales-payment-input">
                                        <option value="">-- Pilih Pasien --</option>
                                        @foreach($pasiens as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="sales-payment-box mt-3">
                                    <label class="sales-payment-label">Foto Resep (Opsional)</label>
                                    <input type="file" name="foto_resep" id="foto_resep" class="form-control sales-payment-input" accept="image/*">
                                    <small class="text-muted text-xs" style="display:block; margin-top:5px;">Unggah foto resep.</small>
                                </div>



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
                                        value="{{ old('nominal_bayar', 0 ) }}"
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

    // --- QUICK ADD PASIEN ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
    const formPasien = document.getElementById('formQuickPasien');
    if(formPasien) {
        formPasien.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = this.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = 'Menyimpan...';
            btnSubmit.disabled = true;

            fetch("{{ route('pasiens.quick-store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                    nama: document.getElementById('quickPasienNama').value,
                    tanggal_lahir: document.getElementById('quickPasienTglLahir').value,
                    jenis_kelamin: document.getElementById('quickPasienJK').value,
                    no_telp: document.getElementById('quickPasienTelp').value,
                    alamat: document.getElementById('quickPasienAlamat').value
                })

            }).then(res => res.json()).then(data => {
                if(data.success) {
                    const select = document.getElementById('pasien_id');
                    select.add(new Option(data.data.nama, data.data.id));
                    select.value = data.data.id;
                    formPasien.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalAddPasien')).hide();
                    alert('Pasien berhasil ditambahkan!');
                }
            }).finally(() => {
                btnSubmit.innerHTML = 'Simpan Pasien';
                btnSubmit.disabled = false;
            });
        });
    }

    // --- QUICK ADD DOKTER ---
    const formDokter = document.getElementById('formQuickDokter');
    if(formDokter) {
        formDokter.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = this.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = 'Menyimpan...';
            btnSubmit.disabled = true;

            fetch("{{ route('dokters.quick-store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                    nama: document.getElementById('quickDokterNama').value,
                    sip: document.getElementById('quickDokterSip').value,
                    no_telp: document.getElementById('quickDokterTelp').value,
                    alamat: document.getElementById('quickDokterAlamat').value
                })

            }).then(res => res.json()).then(data => {
                if(data.success) {
                    const select = document.getElementById('dokter_id');
                    select.add(new Option(data.data.nama, data.data.id));
                    select.value = data.data.id;
                    formDokter.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalAddDokter')).hide();
                    alert('Dokter berhasil ditambahkan!');
                }
            }).finally(() => {
                btnSubmit.innerHTML = 'Simpan Dokter';
                btnSubmit.disabled = false;
            });
        });
    }

    });
</script>
<!-- Modal Tambah Pasien -->
<div class="modal fade" id="modalAddPasien" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pasien Cepat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="formQuickPasien">
              <div class="mb-2">
                  <label>Nama Pasien *</label>
                  <input type="text" id="quickPasienNama" class="form-control" required>
              </div>
              <div class="mb-2">
                  <label>Tanggal Lahir</label>
                  <input type="date" id="quickPasienTglLahir" class="form-control">
              </div>
              <div class="mb-2">
                  <label>Jenis Kelamin *</label>
                  <select id="quickPasienJK" class="form-control" required>
                      <option value="L">Laki-laki</option>
                      <option value="P">Perempuan</option>
                  </select>
              </div>
              <div class="mb-2">
                  <label>No. HP</label>
                  <input type="text" id="quickPasienTelp" class="form-control">
              </div>
              <div class="mb-2">
                  <label>Alamat</label>
                  <textarea id="quickPasienAlamat" class="form-control" rows="2"></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100 mt-2">Simpan Pasien</button>
          </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Dokter -->
<div class="modal fade" id="modalAddDokter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Dokter Cepat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="formQuickDokter">
              <div class="mb-2">
                  <label>Nama Dokter *</label>
                  <input type="text" id="quickDokterNama" class="form-control" required>
              </div>
              <div class="mb-2">
                  <label>No. SIP</label>
                  <input type="text" id="quickDokterSip" class="form-control" placeholder="Opsional (Jika diharuskan)">
              </div>
              <div class="mb-2">
                  <label>No. HP</label>
                  <input type="text" id="quickDokterTelp" class="form-control">
              </div>
              <div class="mb-2">
                  <label>Alamat Praktik</label>
                  <textarea id="quickDokterAlamat" class="form-control" rows="2"></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100 mt-2">Simpan Dokter</button>
          </form>
      </div>
    </div>
  </div>
</div>
@endsection