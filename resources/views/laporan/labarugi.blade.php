@extends('layout.conquer')

@section('content')
    <style>
        @media print {
            /* Hide the Sidebar, Topbar, and any other non-essential UI */
            .sidebar,
            .topbar,
            .footer {
                display: none !important;
            }
            /* Make the main content take up full width */
            .main-wrapper {
                margin-left: 0 !important;
                padding-top: 0 !important;
                width: 100% !important;
            }
            body { overflow: visible !important; }
            button, a.btn { display: none !important; }
            .no-print { display: none !important; }
        }
        .card-summary {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 16px 20px;
            background: #fff;
        }
        .card-summary .label { font-size: 13px; color: #6c757d; margin-bottom: 4px; }
        .card-summary .value { font-size: 22px; font-weight: 700; }
        .text-profit { color: #198754; }
        .text-loss   { color: #dc3545; }
        .badge-profit { background-color: #d1e7dd; color: #0f5132; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .badge-loss   { background-color: #f8d7da; color: #842029; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    </style>

    <div class="container">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 border-b pb-2">Laporan Laba Rugi Kotor</h1>
        <p class="text-gray-500 mb-4">Perhitungan gross profit berdasarkan HPP Metode Average (Weighted Average Cost)</p>

        {{-- Filter Form --}}
        <div class="no-print mb-4">
            <form method="GET" action="{{ route('laporan.labarugi') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                    <label class="form-label mb-1">Filter Periode</label>
                    <select name="filter" class="form-control">
                        <option value="day"   {{ $filter == 'day'   ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week"  {{ $filter == 'week'  ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="year"  {{ $filter == 'year'  ? 'selected' : '' }}>Tahun Ini</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div>
                    <label class="form-label mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4 g-3 no-print">
            <div class="col-md-4">
                <div class="card-summary">
                    <div class="label">Total Penjualan</div>
                    <div class="value text-primary">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-summary">
                    <div class="label">Total HPP (Modal)</div>
                    <div class="value text-warning">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-summary">
                    <div class="label">Laba Kotor</div>
                    <div class="value {{ $totalLaba >= 0 ? 'text-profit' : 'text-loss' }}">
                        Rp {{ number_format($totalLaba, 0, ',', '.') }}
                    </div>
                    @if ($totalPenjualan > 0)
                        @php $margin = ($totalLaba / $totalPenjualan) * 100; @endphp
                        <span class="{{ $margin >= 0 ? 'badge-profit' : 'badge-loss' }}">
                            Margin: {{ number_format($margin, 1) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Per-Produk Summary Table --}}
        <h5 class="mt-4 mb-2 fw-bold">Ringkasan Per Produk</h5>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Total Qty Terjual</th>
                    <th>Total Penjualan</th>
                    <th>Total HPP</th>
                    <th>Laba Kotor</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summaryProduk as $i => $row)
                    @php
                        $laba = $row->total_penjualan - $row->total_hpp;
                        $margin = $row->total_penjualan > 0
                            ? ($laba / $row->total_penjualan) * 100
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->nama_produk }}</td>
                        <td>{{ number_format($row->total_qty, 0, ',', '.') }} {{ $row->nama_satuan ?? '' }}</td>
                        <td>Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</td>
                        <td class="{{ $laba >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                            Rp {{ number_format($laba, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="{{ $margin >= 0 ? 'badge-profit' : 'badge-loss' }}">
                                {{ number_format($margin, 1) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    @if (($totalEmbalaseRacikan ?? 0) <= 0)
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Tidak ada data transaksi pada periode ini.
                            </td>
                        </tr>
                    @endif
                @endforelse

                @if (($totalEmbalaseRacikan ?? 0) > 0)
                    <tr>
                        <td>-</td>
                        <td>Biaya Embalase Racikan</td>
                        <td>-</td>
                        <td>Rp {{ number_format($totalEmbalaseRacikan, 0, ',', '.') }}</td>
                        <td>Rp 0</td>
                        <td>
                            <strong>Rp {{ number_format($totalEmbalaseRacikan, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <span class="badge-profit">100%</span>
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td colspan="3">Total</td>
                    <td>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                    <td class="{{ $totalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($totalLaba, 0, ',', '.') }}
                    </td>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>

        {{-- Riwayat Perubahan HPP --}}
        <h5 class="mt-5 mb-2 fw-bold">Riwayat Perubahan HPP (10 Terakhir)</h5>
        <p class="text-muted small">Log otomatis setiap kali terjadi pembelian yang mempengaruhi HPP Average produk.</p>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Tipe</th>
                    <th>Stok Lama</th>
                    <th>Harga Lama</th>
                    <th>Mutasi Stok</th>
                    <th>Harga Baru</th>
                    <th>HPP Avg Baru</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hppHistory as $h)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $h->produk->nama ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $h->tipe === 'pembelian' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                {{ ucfirst($h->tipe) }}
                            </span>
                        </td>
                        <td>{{ number_format($h->stok_lama, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($h->harga_lama, 0, ',', '.') }}</td>
                        <td>{{ number_format($h->stok_baru, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($h->harga_baru, 0, ',', '.') }}</td>
                        <td class="fw-bold text-primary">Rp {{ number_format($h->hpp_avg_baru, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada riwayat perubahan HPP.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Action Buttons --}}
        <div class="no-print mt-3">
            <a href="{{ route('laporan.labarugi.csv', ['filter' => $filter, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="btn btn-success">Download CSV</a>
            <button onclick="window.print()" class="btn btn-primary ml-2">Print Laporan</button>
        </div>
    </div>
@endsection
