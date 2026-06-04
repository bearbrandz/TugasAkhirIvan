@extends('layout.conquer')

@section('title', 'Ubah Racikan')

@section('content')
<style>
    .racikan-form-page {
        max-width: 100%;
    }

    .racikan-header {
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .racikan-header h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 800;
        color: #f8fafc;
    }

    .racikan-header p {
        margin: 8px 0 0;
        color: #94a3b8;
    }

    .racikan-card {
        background: #162033;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .racikan-section-title {
        font-size: 17px;
        font-weight: 800;
        color: #f8fafc;
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
        color: #f8fafc;
        font-weight: 700;
    }

    .racikan-form-group small {
        color: #94a3b8;
    }

    .racikan-form-control {
        width: 100%;
        background: #111827 !important;
        color: #f8fafc !important;
        border: 1px solid rgba(148, 163, 184, 0.28) !important;
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
        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 12px;
        padding: 12px;
    }

    .komposisi-row select,
    .komposisi-row input {
        height: 42px;
    }

    .komposisi-help {
        margin-top: 8px;
        color: #94a3b8;
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
        border: 1px solid rgba(234, 179, 8, 0.28);
        color: #fde68a;
        border-radius: 10px;
        padding: 12px;
        font-size: 13px;
        line-height: 1.5;
    }

    .current-resep-box {
        margin-top: 10px;
        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 10px;
        padding: 10px;
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
        <h1>Ubah Racikan</h1>
        <p>Perbarui data racikan, bukti resep, dan komposisi produk.</p>
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

    <form method="POST" action="{{ route('racikans.update', $datas->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="racikan-card">
            <div class="racikan-section-title">Informasi Racikan</div>

            <div class="racikan-form-grid">
                <div class="racikan-form-group">
                    <label>Nama Racikan</label>
                    <input
                        type="text"
                        name="nama"
                        class="racikan-form-control"
                        placeholder="Masukkan nama racikan"
                        value="{{ old('nama', $datas->nama) }}"
                        required
                    >
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
                        value="{{ old('biaya_embalase', $datas->biaya_embalase) }}"
                        required
                    >
                </div>

                <div class="racikan-form-group full">
                    <label>Deskripsi Racikan</label>
                    <textarea
                        name="deskripsi"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Masukkan deskripsi racikan"
                    >{{ old('deskripsi', $datas->deskripsi) }}</textarea>
                </div>

                <div class="racikan-form-group full">
                    <label>Aturan Pemakaian</label>
                    <textarea
                        name="aturan_pakai"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Contoh: 3x1 sehari setelah makan"
                        required
                    >{{ old('aturan_pakai', $datas->aturan_pakai) }}</textarea>
                </div>

                <div class="racikan-form-group">
                    <label>Tanggal Pengambilan</label>
                    <input
                        type="date"
                        name="tgl_ambil"
                        class="racikan-form-control"
                        value="{{ old('tgl_ambil', $datas->tgl_ambil) }}"
                    >
                </div>

                <div class="racikan-form-group">
                    <label>Bukti Resep</label>
                    <input
                        type="file"
                        name="bukti_resep"
                        class="racikan-form-control"
                        accept="image/*"
                    >
                    <small>Kosongkan jika tidak ingin mengganti bukti resep.</small>

                    @if (!empty($datas->bukti_resep))
                        <div class="current-resep-box">
                            <small>Bukti resep saat ini:</small><br>
                            <a
                                href="{{ asset('storage/' . $datas->bukti_resep) }}"
                                target="_blank"
                                class="btn btn-info btn-sm mt-2"
                            >
                                Lihat Bukti Resep
                            </a>
                        </div>
                    @endif
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
                    <label>Nama Dokter</label>
                    <input
                        type="text"
                        name="nama_dokter"
                        class="racikan-form-control"
                        placeholder="Masukkan nama dokter"
                        value="{{ old('nama_dokter', $datas->nama_dokter) }}"
                    >
                </div>

                <div class="racikan-form-group">
                    <label>Nama Pasien</label>
                    <input
                        type="text"
                        name="nama_pasien"
                        class="racikan-form-control"
                        placeholder="Masukkan nama pasien"
                        value="{{ old('nama_pasien', $datas->nama_pasien) }}"
                    >
                </div>

                <div class="racikan-form-group">
                    <label>Alamat Dokter</label>
                    <textarea
                        name="alamat_dokter"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Masukkan alamat dokter"
                    >{{ old('alamat_dokter', $datas->alamat_dokter) }}</textarea>
                </div>

                <div class="racikan-form-group">
                    <label>Alamat Pasien</label>
                    <textarea
                        name="alamat_pasien"
                        class="racikan-form-control"
                        rows="3"
                        placeholder="Masukkan alamat pasien"
                    >{{ old('alamat_pasien', $datas->alamat_pasien) }}</textarea>
                </div>
            </div>
        </div>

        <div class="racikan-card">
            <div class="racikan-section-title">Produk Komposisi</div>

            <div id="komposisi-wrapper" class="komposisi-wrapper">
                @php
                    $oldProdukIds = old('produks_id');
                    $oldQuantities = old('quantity');

                    if ($oldProdukIds) {
                        $rows = collect($oldProdukIds)->map(function ($produkId, $index) use ($oldQuantities) {
                            return (object) [
                                'produks_id' => $produkId,
                                'quantity' => $oldQuantities[$index] ?? '',
                                'produk' => null,
                            ];
                        });
                    } else {
                        $rows = $komposisi->count() > 0
                            ? $komposisi
                            : collect([(object) [
                                'produks_id' => null,
                                'quantity' => '',
                                'produk' => null,
                            ]]);
                    }
                @endphp

                @foreach ($rows as $k)
                    <div class="komposisi-row">
                        <select name="produks_id[]" class="racikan-form-control produk-komposisi" required>
                            <option value="">-- Pilih Produk --</option>

                            @php
                                $currentProdukId = $k->produks_id ?? null;
                                $selectedExists = $produks->contains('id', $currentProdukId);
                            @endphp

                            @if ($currentProdukId && !$selectedExists && !empty($k->produk))
                                <option value="{{ $k->produk->id }}" selected>
                                    {{ $k->produk->nama }}
                                    - {{ ucfirst($k->produk->golongan ?? '-') }}
                                    - Stok: tidak tersedia
                                </option>
                            @endif

                            @foreach ($produks as $produk)
                                <option
                                    value="{{ $produk->id }}"
                                    {{ (string) $currentProdukId === (string) $produk->id ? 'selected' : '' }}
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
                            value="{{ $k->quantity ?? '' }}"
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
                    Simpan Perubahan
                </button>

                <a href="{{ route('racikans.index') }}" class="btn btn-default">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('komposisi-wrapper');
        const btnTambah = document.getElementById('btnTambahKomposisi');

        if (!wrapper || !btnTambah) return;

        btnTambah.addEventListener('click', function () {
            const firstRow = wrapper.querySelector('.komposisi-row');
            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('select, input').forEach(function (input) {
                input.value = '';
            });

            wrapper.appendChild(newRow);
        });

        wrapper.addEventListener('click', function (event) {
            if (!event.target.classList.contains('btn-komposisi-remove')) {
                return;
            }

            const rows = wrapper.querySelectorAll('.komposisi-row');

            if (rows.length > 1) {
                event.target.closest('.komposisi-row').remove();
            } else {
                rows[0].querySelectorAll('select, input').forEach(function (input) {
                    input.value = '';
                });
            }
        });
    });
</script>
@endsection