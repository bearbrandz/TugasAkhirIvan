@extends('layout.conquer')
@section('title')
@section('content')

    @if ($expired_batches->count() > 0)
        <div class="alert alert-danger mb-4">
            <strong>Produk Kadaluarsa:</strong>
            <ul>
                @foreach ($expired_batches as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $criticalStockMessages = collect($cirital_stocks ?? [])->filter(function ($msg) {
            $text = strtolower((string) $msg);

            return !str_contains($text, 'tidak ada produk')
                && !str_contains($text, 'stok kritis (0)')
                && !str_contains($text, 'stok sedikit (0)');
        });
    @endphp

    @if ($criticalStockMessages->count() > 0)
        <div class="alert alert-danger mb-4">
            <strong>Produk Stok Sedikit:</strong>
            <ul>
                @foreach ($criticalStockMessages as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($sixmonthsexpired_batches->count() > 0)
        <div class="alert alert-warning mb-4">
            <strong>Batch Akan Kadaluarsa:</strong>
            <ul>
                @foreach ($sixmonthsexpired_batches as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="mb-4">Dashboard</h1>

    <div class="row">
        <!-- LEFT SIDE: Cards & Charts -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success">Total Penjualan Bulan Ini</h5>
                    <p class="card-text fw-bold">Rp{{ number_format($totalSalesRupiah, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Pembelian Bulan Ini</h5>
                    <p class="card-text fw-bold">Rp{{ number_format($totalPurchasesRupiah, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Penjualan per Minggu Bulan Ini</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pembelian per Minggu Bulan Ini</h5>
                    <canvas id="purchasesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Produk List & Filter -->
        <div class="col-lg-8 mb-4">
            <form method="GET" action="{{ route('homeProduk') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari Produk..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-secondary">Cari</button>
                </div>
            </form>

            <form method="GET" action="{{ url('/home') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <select name="sort_by" class="form-select">
                        <option value="nama" {{ request('sort_by') === 'nama' ? 'selected' : '' }}>Nama</option>
                        <option value="total_stok" {{ request('sort_by') === 'total_stok' ? 'selected' : '' }}>Stok</option>
                        <option value="sellingprice" {{ request('sort_by') === 'sellingprice' ? 'selected' : '' }}>Harga</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="sort_order" class="form-select">
                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>A-Z / Terkecil</option>
                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Z-A / Terbesar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Sort</button>
                </div>
            </form>

            <h4 class="mb-3">Daftar Produk</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                @foreach ($datas as $d)
                    <div class="col">
                        <a href="{{ route('produks.show', $d->id) }}" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="mb-2">
                                        <h6 class="card-title mb-1">{{ $d->nama }}</h6>
                                    </div>
                                    <div class="text-end small text-muted mt-auto">
                                        <p class="mb-0">Stok: {{ $d->total_stok ?? 0 }}</p>
                                        <p class="mb-0">Harga: Rp{{ number_format($d->final_price ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $datas->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabelsSales),
                datasets: [{
                    label: 'Produk Terjual per Minggu',
                    data: @json($chartDataSales),
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        const purchasesCtx = document.getElementById('purchasesChart').getContext('2d');
        new Chart(purchasesCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabelsPurchases),
                datasets: [{
                    label: 'Produk Dibeli per Minggu',
                    data: @json($chartDataPurchases),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
@endsection
