@extends('layout.conquer')

@section('title', 'Halaman Pembelian Produk')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h4 text-white mb-1">Halaman Pembelian Produk</h1>
            <p class="text-muted mb-0">
                Pilih produk yang sudah ada untuk beli lagi, atau tambahkan produk baru.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalProdukBaru"
        >
            <i class="fas fa-plus me-1"></i>
            Beli Produk Baru
        </button>
    </div>

    <div class="row g-4">
        {{-- LEFT: DAFTAR PRODUK --}}
        <div class="col-lg-8">

            {{-- SEARCH --}}
            <form method="GET" action="{{ route('notabelis.create') }}" class="mb-4">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari produk..."
                    >
                    <button class="btn btn-primary" type="submit">
                        Cari
                    </button>
                </div>
            </form>

            {{-- INFO PEGAWAI --}}
            <div class="mb-3">
                <p class="mb-0">
                    <strong>Pegawai:</strong> {{ auth()->user()->nama }}
                </p>
            </div>

            {{-- PRODUCT LIST --}}
            <div class="row g-3">
                @forelse ($prod as $p)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 purchase-card">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-white mb-3">
                                    {{ $p->nama }}
                                </h5>

                                <p class="mb-1">
                                    <strong>Stok Tersedia:</strong>
                                    {{ number_format($p->total_stok ?? 0, 0, ',', '.') }}
                                    {{ $p->nama_satuan_jual ?? 'satuan belum diset' }}
                                    @if(empty($p->satuan_jual_id))
                                        <p class="text-warning small mb-1">
                                            Produk ini belum memiliki satuan stok/jual utama.
                                        </p>
                                    @endif
                                </p>

                                <p class="mb-1">
                                    <strong>Harga Beli Terakhir:</strong>
                                    Rp{{ number_format($p->harga_beli_terakhir ?? 0, 0, ',', '.') }}
                                </p>

                                <p class="mb-1">
                                    <strong>Margin Jual:</strong>
                                    {{ $p->sellingprice ?? 0 }}%
                                </p>

                                @if(!empty($p->kadaluarsa_terakhir))
                                    <p class="mb-3">
                                        <strong>Kadaluarsa Terakhir:</strong>
                                        {{ \Carbon\Carbon::parse($p->kadaluarsa_terakhir)->format('d/m/Y') }}
                                    </p>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-success w-100 mt-auto"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalBeliProduk{{ $p->id }}"
                                >
                                    <i class="fas fa-cart-plus me-1"></i>
                                    Beli / Beli Lagi
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL BELI PRODUK EXISTING --}}
                    <div class="modal fade" id="modalBeliProduk{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('notabelis.cart') }}" class="form-hitung-harga">
                                    @csrf

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Beli Produk: {{ $p->nama }}
                                        </h5>
                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="{{ $p->id }}">
                                        <input type="hidden" name="produk_id" value="{{ $p->id }}">
                                        <input type="hidden" name="nama" value="{{ $p->nama }}">
                                        <input type="hidden" class="produk-satuan-jual-id" value="{{ $p->satuan_jual_id }}">
                                        <input type="hidden" class="produk-satuan-jual-nama" value="{{ $p->nama_satuan_jual ?? '' }}">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Harga Beli per Unit</label>
                                                <input
                                                    type="number"
                                                    class="form-control input-harga-beli"
                                                    name="unitprice"
                                                    min="1"
                                                    step="0.01"
                                                    value="{{ $p->harga_beli_terakhir ?? '' }}"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Margin Harga Jual (%)</label>
                                                <input
                                                    type="number"
                                                    class="form-control input-margin-jual"
                                                    name="sellingprice"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $p->sellingprice ?? 0 }}"
                                                    required
                                                >
                                                <small class="form-text">
                                                    Contoh: 20 berarti harga jual = harga beli + 20%.
                                                </small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Preview Harga Jual</label>
                                                <input
                                                    type="text"
                                                    class="form-control preview-harga-jual"
                                                    value="Rp 0"
                                                    readonly
                                                >
                                            </div>

                                            <div class="col-md-6">
                                            <label class="form-label">Jumlah Masuk</label>
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    class="form-control"
                                                    min="1"
                                                    value="1"
                                                    required
                                                >
                                                <small class="form-text text-muted">
                                                    Isi jumlah barang yang masuk ke stok. Contoh: 100 tablet.
                                                </small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Kadaluarsa</label>
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    name="tgl_kadaluarsa"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Produksi</label>
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    name="tgl_produksi"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Distributor</label>
                                                <select name="distributors_id" class="form-select" required>
                                                    @foreach ($distributors as $d)
                                                        <option value="{{ $d->id }}">
                                                            {{ $d->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Satuan Beli</label>
                                                <select name="satuans_id" class="form-select satuan-input-select" required>
                                                    @foreach ($satuans as $s)
                                                        <option value="{{ $s->id }}">
                                                            {{ $s->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Gudang</label>
                                                <select name="gudangs_id" class="form-select" required>
                                                    @foreach ($gudangs as $g)
                                                        <option value="{{ $g->id }}">
                                                            {{ $g->lokasi }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="alert alert-info mb-0">
                                                    <strong>Catatan Satuan:</strong><br>
                                                    Jika pembelian dicatat langsung dalam satuan stok, biarkan konversi tetap
                                                    <strong>Tidak Ada</strong>. Contoh: beli 100 tablet, maka isi Jumlah Masuk = 100.
                                                    <br>
                                                    Gunakan konversi hanya jika pembelian dari kemasan besar, misalnya 1 box berisi 100 tablet.
                                                </div>
                                            </div>

                                            @if (isset($satuanKonversis) && count($satuanKonversis) > 0)
                                                <div class="col-md-4">
                                                    <label class="form-label">Konversi ke Satuan Stok/Jual</label>

                                                    <select name="satuan_konversi_id" class="form-select konversi-select">
                                                        <option
                                                            value=""
                                                            data-konversi="1"
                                                            data-satuan-dari=""
                                                            data-satuan-ke=""
                                                            data-satuan-dari-nama=""
                                                            data-satuan-ke-nama=""
                                                        >
                                                            Tidak Ada - satuan beli sama dengan satuan stok/jual
                                                        </option>

                                                        @foreach ($satuanKonversis as $konversi)
                                                            @if($konversi->satuanDari && $konversi->satuanKe)
                                                                <option
                                                                    value="{{ $konversi->id }}"
                                                                    data-konversi="{{ $konversi->nilai_konversi }}"
                                                                    data-satuan-dari="{{ $konversi->satuan_besar_id }}"
                                                                    data-satuan-ke="{{ $konversi->satuan_kecil_id }}"
                                                                    data-satuan-dari-nama="{{ $konversi->satuanDari->nama }}"
                                                                    data-satuan-ke-nama="{{ $konversi->satuanKe->nama }}"
                                                                >
                                                                    {{ $konversi->satuanDari->nama }}
                                                                    →
                                                                    {{ $konversi->satuanKe->nama }}
                                                                    (1 {{ $konversi->satuanDari->nama }} = {{ $konversi->nilai_konversi }} {{ $konversi->satuanKe->nama }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>

                                                    <input type="hidden" name="jumlah_konversi" value="1">

                                                    <small class="form-text text-muted konversi-helper">
                                                        Total stok masuk akan mengikuti jumlah input.
                                                    </small>
                                                </div>
                                            @else
                                                <input type="hidden" name="satuan_konversi_id" value="">
                                                <input type="hidden" name="jumlah_konversi" value="1">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal"
                                        >
                                            Batal
                                        </button>

                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-cart-plus me-1"></i>
                                            Tambah ke Keranjang
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

            {{-- PAGINATION --}}
            <div class="mt-4">
                {{ $prod->links('pagination::bootstrap-5') }}
            </div>
        </div>

        {{-- RIGHT: KERANJANG --}}
        <div class="col-lg-4">
            @if (isset($cart) && count($cart) > 0)
                <div class="card cart-card">
                    <div class="card-header border-secondary">
                        <h5 class="mb-0 text-white">Keranjang Pembelian</h5>
                    </div>

                    <div class="card-body p-3">
                        <ul class="list-unstyled mb-0">
                            @foreach ($cart as $key => $item)
                                @php
                                    $satuanObj = \App\Models\Satuan::find($item['satuans_id']);
                                @endphp

                                <li class="mb-3">
                                    <strong>{{ $item['nama'] }}</strong>
                                    <br>
                                    {{ $item['quantity'] }}
                                    {{ $satuanObj->nama ?? '' }}
                                    x Rp{{ number_format($item['unitprice'], 0, ',', '.') }}

                                    @if (!empty($item['sellingprice']))
                                        <br>
                                        <small class="text-info">
                                            Margin Jual: {{ $item['sellingprice'] }}%
                                        </small>
                                    @endif

                                    @if (!empty($item['jumlah_konversi']) && $item['jumlah_konversi'] > 1)
                                        <br>
                                        <small class="text-info">
                                            Konversi:
                                            {{ $item['quantity'] }}
                                            x
                                            {{ $item['jumlah_konversi'] }}
                                            =
                                            {{ $item['quantity'] * $item['jumlah_konversi'] }}
                                            satuan dasar
                                        </small>
                                    @endif

                                    <br>
                                    <small>
                                        Kadaluarsa:
                                        {{ $item['tgl_kadaluarsa'] ? \Carbon\Carbon::parse($item['tgl_kadaluarsa'])->format('d-m-Y') : 'Tidak Ada' }}
                                    </small>

                                    @if(auth()->user()->tipe_user === 'admin')
                                        <form
                                            method="POST"
                                            action="{{ route('notabeliscart.delete', ['id' => $key]) }}"
                                            class="d-inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm mt-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card-footer border-secondary">
                        <form method="POST" action="{{ route('notabelis.store') }}">
                            @csrf

                            <input type="hidden" name="pegawai_id" value="{{ auth()->user()->id }}">

                            @foreach ($cart as $item)
                                <input type="hidden" name="tgl_produksi[]" value="{{ $item['tgl_produksi'] }}">
                                <input type="hidden" name="tgl_kadaluarsa[]" value="{{ $item['tgl_kadaluarsa'] }}">
                                <input type="hidden" name="produk_id[]" value="{{ $item['id'] }}">
                                <input type="hidden" name="quantity[]" value="{{ $item['quantity'] }}">
                                <input type="hidden" name="unitprice[]" value="{{ $item['unitprice'] }}">
                                <input type="hidden" name="sellingprice[]" value="{{ $item['sellingprice'] ?? 0 }}">
                                <input type="hidden" name="distributors_id[]" value="{{ $item['distributors_id'] }}">
                                <input type="hidden" name="satuans_id[]" value="{{ $item['satuans_id'] }}">
                                <input type="hidden" name="gudangs_id[]" value="{{ $item['gudangs_id'] }}">
                                <input type="hidden" name="satuan_konversi_id[]" value="{{ $item['satuan_konversi_id'] ?? '' }}">
                                <input type="hidden" name="konversi_ke_satuan_id[]" value="{{ $item['konversi_ke_satuan_id'] ?? '' }}">
                                <input type="hidden" name="jumlah_konversi[]" value="{{ $item['jumlah_konversi'] ?? 1 }}">
                            @endforeach

                            <button class="btn btn-primary w-100 mt-2" type="submit">
                                Simpan Pembelian
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    Keranjang pembelian masih kosong.
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL BELI PRODUK BARU --}}
    <div class="modal fade" id="modalProdukBaru" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('notabelis.beliProdukBaru') }}" class="form-hitung-harga">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Beli Produk Baru</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="pegawai_id" value="{{ auth()->user()->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Golongan Produk</label>
                                <select class="form-select" name="golongan" required>
                                    <option value="bebas">Bebas</option>
                                    <option value="terbatas">Terbatas</option>
                                    <option value="keras">Keras</option>
                                    <option value="narkotika">Narkotika</option>
                                    <option value="psikotropika">Psikotropika</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Deskripsi Produk</label>
                                <textarea
                                    class="form-control"
                                    name="deskripsi"
                                    rows="3"
                                    required
                                ></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="stok" class="form-control" min="1" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Harga Beli per Unit</label>
                                <input
                                    type="number"
                                    name="unitprice"
                                    id="hargaBeliProdukBaru"
                                    class="form-control input-harga-beli"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Margin Harga Jual (%)</label>
                                <input
                                    type="number"
                                    name="sellingprice"
                                    id="marginProdukBaru"
                                    class="form-control input-margin-jual"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Preview Harga Jual</label>
                                <input
                                    type="text"
                                    id="previewProdukBaru"
                                    class="form-control preview-harga-jual"
                                    value="Rp 0"
                                    readonly
                                >
                            </div>

                            <div class="col-md-4">
                            <label class="form-label">Satuan Beli</label>
                            <select class="form-select satuan-input-select" name="satuans" required>
                                    @foreach ($satuans as $s)
                                        <option value="{{ $s->id }}">
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Satuan Stok/Jual Utama</label>
                                <select class="form-select satuan-jual-select" name="satuan_jual_id" required>
                                    @foreach ($satuans as $s)
                                        <option value="{{ $s->id }}">
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Satuan yang dipakai untuk stok dan penjualan produk ini.
                                </small>
                            </div>

                            @if (isset($satuanKonversis) && count($satuanKonversis) > 0)
                                <div class="col-md-4">
                                    <label class="form-label">Konversi Satuan</label>

                                    <select name="satuan_konversi_id" class="form-select konversi-select">
                                        <option value="" data-konversi="1">
                                            Tidak Ada - Jumlah masuk langsung menjadi stok
                                        </option>

                                        @foreach ($satuanKonversis as $konversi)
                                            @if($konversi->satuanDari && $konversi->satuanKe)
                                                <option
                                                    value="{{ $konversi->id }}"
                                                    data-konversi="{{ $konversi->nilai_konversi }}"
                                                    data-satuan-dari="{{ $konversi->satuan_besar_id }}"
                                                    data-satuan-ke="{{ $konversi->satuan_kecil_id }}"
                                                >
                                                    {{ $konversi->satuanDari->nama }}
                                                    →
                                                    {{ $konversi->satuanKe->nama }}
                                                    (1 {{ $konversi->satuanDari->nama }} = {{ $konversi->nilai_konversi }} {{ $konversi->satuanKe->nama }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="jumlah_konversi" value="1">

                                    <small class="form-text text-muted konversi-helper">
                                        Total stok masuk akan mengikuti Quantity.
                                    </small>
                                </div>
                            @else
                                <input type="hidden" name="satuan_konversi_id" value="">
                                <input type="hidden" name="jumlah_konversi" value="1">
                            @endif

                            <div class="col-md-4">
                                <label class="form-label">Distributor</label>
                                <select name="distributors" class="form-select" required>
                                    @foreach ($distributors as $d)
                                        <option value="{{ $d->id }}">
                                            {{ $d->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gudang Produk</label>
                                <select class="form-select" name="gudangs" required>
                                    @foreach ($gudangs as $g)
                                        <option value="{{ $g->id }}">
                                            {{ $g->lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Kadaluarsa</label>
                                <input type="date" name="tgl_kadaluarsa" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Produksi</label>
                                <input type="date" name="tgl_produksi" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>

                        <button class="btn btn-success" type="submit">
                            Simpan Produk Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(number || 0));
    }

    function hitungPreviewHarga(form) {
        if (!form) return;

        const hargaInput = form.querySelector('.input-harga-beli');
        const marginInput = form.querySelector('.input-margin-jual');
        const previewInput = form.querySelector('.preview-harga-jual');

        if (!hargaInput || !marginInput || !previewInput) return;

        const harga = Number(hargaInput.value || 0);
        const margin = Number(marginInput.value || 0);
        const hargaJual = harga + (harga * margin / 100);

        previewInput.value = formatRupiah(hargaJual);
    }

    function getSatuanBeliSelect(form) {
        return form.querySelector('select[name="satuans_id"]') ||
               form.querySelector('select[name="satuans"]');
    }

    function getSatuanJualId(form) {
        const hidden = form.querySelector('.produk-satuan-jual-id');
        const select = form.querySelector('select[name="satuan_jual_id"]');

        if (hidden && hidden.value) {
            return String(hidden.value);
        }

        if (select && select.value) {
            return String(select.value);
        }

        return '';
    }

    function getSatuanJualNama(form) {
        const hidden = form.querySelector('.produk-satuan-jual-nama');
        const select = form.querySelector('select[name="satuan_jual_id"]');

        if (hidden && hidden.value) {
            return hidden.value;
        }

        if (select && select.selectedIndex >= 0) {
            return select.options[select.selectedIndex].text.trim();
        }

        return 'satuan stok/jual';
    }

    function getSatuanBeliNama(form) {
        const select = getSatuanBeliSelect(form);

        if (select && select.selectedIndex >= 0) {
            return select.options[select.selectedIndex].text.trim();
        }

        return 'satuan beli';
    }

    function simpanOriginalOptions(selectEl) {
        if (selectEl.dataset.originalOptionsSaved === '1') {
            return;
        }

        const data = [];

        Array.from(selectEl.options).forEach(function (option) {
            data.push({
                value: option.value,
                text: option.text,
                konversi: option.getAttribute('data-konversi') || '1',
                satuanDari: option.getAttribute('data-satuan-dari') || '',
                satuanKe: option.getAttribute('data-satuan-ke') || '',
                satuanDariNama: option.getAttribute('data-satuan-dari-nama') || '',
                satuanKeNama: option.getAttribute('data-satuan-ke-nama') || ''
            });
        });

        selectEl.dataset.originalOptions = JSON.stringify(data);
        selectEl.dataset.originalOptionsSaved = '1';
    }

    function updateHelper(form, selectEl) {
        const helper = form.querySelector('.konversi-helper');
        const qtyInput =
            form.querySelector('input[name="quantity"]') ||
            form.querySelector('input[name="stok"]');

        const inputKonversi = form.querySelector('input[name="jumlah_konversi"]');

        const selected = selectEl.options[selectEl.selectedIndex];
        const nilaiKonversi = Number(selected?.getAttribute('data-konversi') || 1);
        const satuanKeNama = selected?.getAttribute('data-satuan-ke-nama') || getSatuanJualNama(form);

        if (inputKonversi) {
            inputKonversi.value = nilaiKonversi;
        }

        if (!helper) return;

        const qty = Number(qtyInput?.value || 0);

        if (selectEl.value && nilaiKonversi > 1) {
            const total = qty * nilaiKonversi;
            helper.textContent = 'Stok yang masuk = ' + qty + ' x ' + nilaiKonversi + ' = ' + total + ' ' + satuanKeNama + '.';
        } else {
            helper.textContent = 'Stok yang masuk = ' + qty + ' ' + getSatuanJualNama(form) + '.';
        }
    }

    function filterKonversi(form) {
        if (!form) return;

        const satuanBeliSelect = getSatuanBeliSelect(form);
        const konversiSelect = form.querySelector('.konversi-select');

        if (!satuanBeliSelect || !konversiSelect) return;

        simpanOriginalOptions(konversiSelect);

        const satuanBeliId = String(satuanBeliSelect.value || '');
        const satuanJualId = getSatuanJualId(form);
        const originalOptions = JSON.parse(konversiSelect.dataset.originalOptions || '[]');

        konversiSelect.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.setAttribute('data-konversi', '1');
        defaultOption.setAttribute('data-satuan-dari', '');
        defaultOption.setAttribute('data-satuan-ke', '');
        defaultOption.setAttribute('data-satuan-dari-nama', '');
        defaultOption.setAttribute('data-satuan-ke-nama', '');

        if (!satuanJualId) {
            defaultOption.textContent = 'Produk belum punya satuan stok/jual';
            konversiSelect.appendChild(defaultOption);
            konversiSelect.setCustomValidity('Produk belum punya satuan stok/jual.');
            updateHelper(form, konversiSelect);
            return;
        }

        if (satuanBeliId === satuanJualId) {
            defaultOption.textContent = 'Tidak Ada - satuan beli sama dengan satuan stok/jual';
            konversiSelect.appendChild(defaultOption);
            konversiSelect.setCustomValidity('');
            updateHelper(form, konversiSelect);
            return;
        }

        const cocok = originalOptions.filter(function (opt) {
            return opt.value !== '' &&
                String(opt.satuanDari) === satuanBeliId &&
                String(opt.satuanKe) === satuanJualId;
        });

        if (cocok.length === 0) {
            defaultOption.textContent = 'Belum ada konversi ' + getSatuanBeliNama(form) + ' → ' + getSatuanJualNama(form);
            konversiSelect.appendChild(defaultOption);
            konversiSelect.setCustomValidity(
                'Belum ada konversi dari ' + getSatuanBeliNama(form) + ' ke ' + getSatuanJualNama(form) + '.'
            );
            updateHelper(form, konversiSelect);
            return;
        }

        defaultOption.textContent = '-- Pilih Konversi --';
        konversiSelect.appendChild(defaultOption);

        cocok.forEach(function (opt) {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            option.setAttribute('data-konversi', opt.konversi);
            option.setAttribute('data-satuan-dari', opt.satuanDari);
            option.setAttribute('data-satuan-ke', opt.satuanKe);
            option.setAttribute('data-satuan-dari-nama', opt.satuanDariNama);
            option.setAttribute('data-satuan-ke-nama', opt.satuanKeNama);

            konversiSelect.appendChild(option);
        });

        konversiSelect.value = cocok[0].value;
        konversiSelect.setCustomValidity('');
        updateHelper(form, konversiSelect);
    }

    function initForm(form) {
        const hargaInput = form.querySelector('.input-harga-beli');
        const marginInput = form.querySelector('.input-margin-jual');
        const satuanBeliSelect = getSatuanBeliSelect(form);
        const satuanJualSelect = form.querySelector('select[name="satuan_jual_id"]');
        const konversiSelect = form.querySelector('.konversi-select');

        if (hargaInput) {
            hargaInput.addEventListener('input', function () {
                hitungPreviewHarga(form);
            });
            hargaInput.addEventListener('change', function () {
                hitungPreviewHarga(form);
            });
        }

        if (marginInput) {
            marginInput.addEventListener('input', function () {
                hitungPreviewHarga(form);
            });
            marginInput.addEventListener('change', function () {
                hitungPreviewHarga(form);
            });
        }

        if (satuanBeliSelect) {
            satuanBeliSelect.addEventListener('change', function () {
                filterKonversi(form);
            });
        }

        if (satuanJualSelect) {
            satuanJualSelect.addEventListener('change', function () {
                filterKonversi(form);
            });
        }

        if (konversiSelect) {
            konversiSelect.addEventListener('change', function () {
                updateHelper(form, konversiSelect);
            });
        }

        const qtyInput =
            form.querySelector('input[name="quantity"]') ||
            form.querySelector('input[name="stok"]');

        if (qtyInput) {
            qtyInput.addEventListener('input', function () {
                if (konversiSelect) {
                    updateHelper(form, konversiSelect);
                }
            });
        }

        hitungPreviewHarga(form);
        filterKonversi(form);
    }

    document.querySelectorAll('.form-hitung-harga').forEach(function (form) {
        initForm(form);
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            this.querySelectorAll('.form-hitung-harga').forEach(function (form) {
                hitungPreviewHarga(form);
                filterKonversi(form);
            });
        });
    });
});
</script>
@endsection