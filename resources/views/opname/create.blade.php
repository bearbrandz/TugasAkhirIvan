@extends('layout.conquer')
@section('title')
@section('content')
    @push('styles')
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Dark mode styling for Select2 */
            .select2-container--default .select2-selection--single {
                background-color: #1a202c;
                border: 1px solid #2d3748;
                color: #e2e8f0;
                height: 38px;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #e2e8f0;
                line-height: 36px;
            }
            .select2-dropdown {
                background-color: #1a202c;
                border: 1px solid #2d3748;
            }
            .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #2d3748;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #4a5568;
            }
            .select2-container--default .select2-search--dropdown .select2-search__field {
                background-color: #2d3748;
                border: 1px solid #4a5568;
                color: #e2e8f0;
            }
        </style>
    @endpush

    @if ($errors->any()) untuk memunculkan error
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Buat Stok Baru</h1>

    <form method="POST" action="{{ route('opnames.store') }}">
        @csrf
        <div class="form-group">
            <label for="batch_id">Pilih Batch Produk</label>
            <select class="form-control" id="batchSelect" name="batch_id" required>
                <option value="">-- Pilih Batch --</option>
                @foreach ($batchs as $b)
                    @php
                        $namaProduk = $b->produks->nama ?? 'Produk tidak ditemukan';
                        $namaSatuan = $b->satuan->nama ?? '';
                        $lokasiGudang = $b->gudang->lokasi ?? '';
                    @endphp

                    @if ($namaProduk)
                        <option value="{{ $b->id }}"
                            data-stok="{{ $b->stok }}"
                            data-produk="{{ $namaProduk }}"
                            data-kadaluarsa="{{ $b->tgl_kadaluarsa }}">
                            {{ $namaProduk }}
                            | Batch #{{ $b->id }}
                            | Stok: {{ $b->stok }} {{ $namaSatuan }}
                            @if($lokasiGudang)
                                | Gudang: {{ $lokasiGudang }}
                            @endif
                            | Exp: {{ $b->tgl_kadaluarsa ?? '-' }}
                        </option>
                    @endif
                @endforeach
            </select>
            <small class="form-text text-muted">Pilih batch yang akan diopname. Hanya batch dengan status tersedia yang ditampilkan.</small>
        </div>
        <div class="form-group">
            <label>Stok Sistem</label>
            <input type="number" class="form-control" id="stokSistem" name="stok_sistem" readonly>
            <small class="form-text text-muted">Jumlah stok yang tercatat di sistem untuk batch ini.</small>
        </div>
        <div class="form-group">
            <label for="stok_fisik">Stok Fisik</label>
            <input type="number" class="form-control" id="stokFisik" name="stok_fisik" placeholder="Masukkan Stok Fisik" required>
            <small class="form-text text-muted">Jumlah stok fisik hasil perhitungan manual.</small>
        </div>
        <div class="form-group">
            <label>Selisih</label>
            <input type="number" class="form-control" id="selisih" name="selisih" readonly>
            <small class="form-text text-muted">Selisih antara stok fisik dan stok sistem.</small>
        </div>
        <div class="form-group">
            <label for="tanggal">Tanggal Opname</label>
            <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal') }}" required>
            <small class="form-text text-muted">Tanggal dilakukannya stok opname.</small>
        </div>
        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
            <small class="form-text text-muted">Isikan catatan atau alasan selisih stok bila ada.</small>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('opnames.index') }}" class="btn btn-secondary ml-2">Batal</a>
    </form>

    @push('scripts')
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            // JavaScript untuk mengisi stok sistem dan menghitung selisih saat batch atau stok fisik berubah
            document.addEventListener('DOMContentLoaded', function () {
                var batchSelect = document.getElementById('batchSelect');
                var stokSistemInput = document.getElementById('stokSistem');
                var stokFisikInput = document.getElementById('stokFisik');
                var selisihInput = document.getElementById('selisih');

                function updateStokSistem() {
                    var selectedOption = batchSelect.options[batchSelect.selectedIndex];
                    var stok = selectedOption ? (selectedOption.getAttribute('data-stok') || 0) : 0;
                    stokSistemInput.value = stok;
                    updateSelisih();
                }

                function updateSelisih() {
                    var sistem = parseFloat(stokSistemInput.value) || 0;
                    var fisik = parseFloat(stokFisikInput.value) || 0;
                    selisihInput.value = fisik - sistem;
                }

                batchSelect.addEventListener('change', updateStokSistem);
                stokFisikInput.addEventListener('input', updateSelisih);

                // Initialize values if a batch is pre-selected
                updateStokSistem();

                // Initialize Select2 for searchable dropdown
                $('#batchSelect').select2({
                    placeholder: "-- Pilih Batch --",
                    allowClear: true,
                    width: '100%'
                });

                // Update stok sistem when Select2 value changes
                $('#batchSelect').on('change', function() {
                    updateStokSistem();
                });
            });
        </script>
    @endpush
@endsection
