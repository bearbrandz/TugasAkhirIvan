@extends('layout.conquer')

@section('title', 'Buat Racikan Baru')

@section('content')
<style>
    .racikan-form-page {
        max-width: 100%;
    }

    .racikan-header {
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e2e8f0;
    }

    .racikan-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #1e293b;
    }

    .racikan-header p {
        margin: 8px 0 0;
        color: #64748b;
    }

    .racikan-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .racikan-section-title {
        font-size: 17px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .racikan-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .racikan-form-group {
        margin-bottom: 14px;
    }

    .racikan-form-group.full {
        grid-column: 1 / -1;
    }

    .racikan-form-group label {
        display: block;
        margin-bottom: 6px;
        color: #1e293b;
        font-weight: 700;
    }

    .racikan-form-group small {
        color: #64748b;
    }

    .racikan-form-control {
        width: 100%;
        background: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 10px 12px;
    }

    .racikan-form-control:focus {
        outline: none;
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.18);
    }

    .komposisi-wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .komposisi-row {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 150px 90px;
        gap: 10px;
        align-items: start;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }

    .komposisi-row select,
    .komposisi-row input {
        height: 42px;
    }

    .komposisi-help {
        margin-top: 8px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    .racikan-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .btn-komposisi-remove {
        height: 42px;
        font-weight: 700;
    }

    .resep-note {
        background: rgba(234, 179, 8, 0.12);
        border: 1px solid rgba(234, 179, 8, 0.35);
        color: #92400e;
        border-radius: 10px;
        padding: 12px;
        font-size: 13px;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .racikan-form-grid {
            grid-template-columns: 1fr;
        }

        .komposisi-row {
            grid-template-columns: 1fr;
        }

        .btn-komposisi-remove,
        .racikan-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="racikan-form-page">
    <div class="racikan-header">
        <h1>Buat Racikan Baru</h1>
        <p>Buat data racikan, isi data pasien/dokter, upload bukti resep bila diperlukan, dan tentukan komposisi produk.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Data belum valid:</strong>
            <ul style="margin-bottom:0;margin-top:8px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('racikans.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="racikan-card">
            <div class="racikan-section-title">Informasi Racikan</div>

            <div class="racikan-form-grid">
                <div class="racikan-form-group">
                    <label>Nama Racikan</label>
                    <input
                        type="text"
                        name="nama"
                        class="racikan-form-control"
                        placeholder="Contoh: Racikan Batuk Anak"
                        value="{{ old('nama') }}"
                        required
                    >
                    <small>Nama racikan yang akan tampil di daftar racikan.</small>
                </div>

                <div class="racikan-form-group">
                    <label>Biaya Embalase</label>
                    <input
                        type="number"
                        name="biaya_embalase"
                        class="racikan-form-control"
                        placeholder="Contoh: 10000"
                        min="0"
                        step="1"
                        value="{{ old('biaya_embalase', 0) }}"
                        required
                    >
                    <small>Biaya jasa/kemasan racikan.</small>
                </div>

                <div class="racikan-form-group full">
                    <label>Deskripsi Racikan</label>
                    <textarea
                        name="deskripsi"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Masukkan deskripsi racikan"
                    >{{ old('deskripsi') }}</textarea>
                </div>

                <div class="racikan-form-group full">
                    <label>Aturan Pemakaian</label>
                    <textarea
                        name="aturan_pakai"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Contoh: 3x1 sehari setelah makan"
                        required
                    >{{ old('aturan_pakai') }}</textarea>
                </div>

                <div class="racikan-form-group">
                    <label>Tanggal Pengambilan</label>
                    <input
                        type="date"
                        name="tgl_ambil"
                        class="racikan-form-control"
                        value="{{ old('tgl_ambil', now()->format('Y-m-d')) }}"
                    >
                    <small>Tanggal pasien mengambil racikan.</small>
                </div>

                <div class="racikan-form-group">
                    <label>Bukti Resep</label>
                    <input
                        type="file"
                        name="bukti_resep"
                        class="racikan-form-control"
                        accept="image/*"
                    >
                    <small>Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</small>
                </div>

                <div class="racikan-form-group full">
                    <div class="resep-note">
                        Bukti resep wajib jika racikan mengandung obat keras, narkotika, atau psikotropika.
                        Jika komposisi hanya obat bebas/terbatas, bukti resep boleh dikosongkan.
                    </div>
                </div>
            </div>
        </div>

        <div class="racikan-card">
            <div class="racikan-section-title">Data Dokter dan Pasien</div>

            <div class="racikan-form-grid">
                <div class="racikan-form-group">
                <label style="display:flex; justify-content:space-between;">
                    <span>Nama Dokter</span> 
                    <button type="button" class="btn btn-sm btn-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAddDokter">+ Baru</button>
                </label>
                <select name="nama_dokter" id="nama_dokter" class="racikan-form-control">
                <option value="">-- Pilih Dokter --</option>
                @foreach($dokters as $d)
                    
                    <option value="{{ $d->nama }}">{{ $d->nama }}</option>
                @endforeach
        </select>
    </div>

    
    <div class="racikan-form-group">
        <label style="display:flex; justify-content:space-between;">
            <span>Nama Pasien</span>
            <button type="button" class="btn btn-sm btn-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAddPasien">+ Baru</button>
        </label>
        <select name="nama_pasien" id="nama_pasien" class="racikan-form-control">
            <option value="">-- Pilih Pasien --</option>
            @foreach($pasiens as $p)
               
                <option value="{{ $p->nama }}">{{ $p->nama }}</option>
            @endforeach
        </select>
    </div>
</div>

             

        <div class="racikan-card">
            <div class="racikan-section-title">Produk Komposisi</div>

            <div id="komposisi-wrapper" class="komposisi-wrapper">
                @php
                    $oldProdukIds = old('produks_id', [null]);
                    $oldQuantities = old('quantity', [null]);
                @endphp

                @foreach ($oldProdukIds as $index => $oldProdukId)
                    <div class="komposisi-row">
                        <select name="produks_id[]" class="racikan-form-control produk-komposisi" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($produks as $produk)
                                <option
                                    value="{{ $produk->id }}"
                                    {{ (string) $oldProdukId === (string) $produk->id ? 'selected' : '' }}
                                >
                                    {{ $produk->nama }}
                                    - {{ ucfirst($produk->golongan ?? '-') }}
                                    - Stok: {{ number_format($produk->total_stok ?? 0, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            type="number"
                            name="quantity[]"
                            class="racikan-form-control jumlah-komposisi"
                            placeholder="Jumlah"
                            min="1"
                            step="1"
                            value="{{ $oldQuantities[$index] ?? '' }}"
                            required
                        >

                        <button type="button" class="btn btn-danger btn-komposisi-remove">
                            Hapus
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="komposisi-help">
                Jumlah komposisi berarti jumlah bahan yang dipakai untuk membuat 1 racikan.
                Contoh: Paracetamol jumlah 1, maka untuk 1 racikan stok Paracetamol berkurang 1.
            </div>

            <div class="racikan-actions">
                <button type="button" id="btnTambahKomposisi" class="btn btn-info">
                    Tambah Produk
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Racikan
                </button>

                <a href="{{ route('racikans.index') }}" class="btn btn-default">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Light mode adjustments for select2 */
    .select2-container--default .select2-selection--single {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        height: 42px;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b;
        line-height: normal;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-dropdown {
        background-color: #ffffff;
        border-color: #cbd5e1;
        color: #1e293b;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #f1f5f9;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #ef4444;
        color: white;
    }
    .select2-search--dropdown .select2-search__field {
        background-color: #ffffff;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Init select2
        $('.produk-komposisi').select2({
            placeholder: "-- Pilih Produk --",
            width: '100%'
        });

        const wrapper = document.getElementById('komposisi-wrapper');
        const btnTambah = document.getElementById('btnTambahKomposisi');

        if (!wrapper || !btnTambah) return;

        btnTambah.addEventListener('click', function () {
            const firstRow = wrapper.querySelector('.komposisi-row');
            
            // Destroy select2 on first row so we clone clean HTML
            $(firstRow).find('.produk-komposisi').select2('destroy');

            const newRow = firstRow.cloneNode(true);

            // Re-init select2 on first row
            $(firstRow).find('.produk-komposisi').select2({
                placeholder: "-- Pilih Produk --",
                width: '100%'
            });

            newRow.querySelectorAll('select, input').forEach(function (input) {
                input.value = '';
            });

            wrapper.appendChild(newRow);

            // Init select2 on new row
            $(newRow).find('.produk-komposisi').select2({
                placeholder: "-- Pilih Produk --",
                width: '100%'
            });
        });

        wrapper.addEventListener('click', function (event) {
            if (!event.target.classList.contains('btn-komposisi-remove')) {
                return;
            }

            const rows = wrapper.querySelectorAll('.komposisi-row');

            if (rows.length > 1) {
                $(event.target.closest('.komposisi-row')).find('.produk-komposisi').select2('destroy');
                event.target.closest('.komposisi-row').remove();
            } else {
                const firstRowSelect = rows[0].querySelector('select');
                $(firstRowSelect).val(null).trigger('change');
                rows[0].querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
            }
        });
    });
</script>
@endpush

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // --- QUICK ADD PASIEN ---
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
                    const select = document.getElementById('nama_pasien');
                    // Trik: value yang di-set adalah data.data.nama, bukan data.data.id
                    select.add(new Option(data.data.nama, data.data.nama));
                    select.value = data.data.nama;
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
                    const select = document.getElementById('nama_dokter');
                    // Trik: value yang di-set adalah data.data.nama, bukan data.data.id
                    select.add(new Option(data.data.nama, data.data.nama));
                    select.value = data.data.nama;
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
@endpush

@endsection