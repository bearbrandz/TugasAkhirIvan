@extends('layout.conquer')

@section('content')
    <style>
        @media print {
            .sidebar,
            .topbar,
            .page-footer,
            .btn,
            button,
            a {
                display: none !important;
            }

            .main-wrapper,
            .page-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                margin: 0 !important;
            }
        }
    </style>

    <div class="container">

        <h1>Laporan Pembelian</h1>
        <p class="text-muted">
            Filter: 
            @if($filter == 'day') Hari Ini
            @elseif($filter == 'week') Minggu Ini
            @elseif($filter == 'month') Bulan Ini
            @elseif($filter == 'year') Tahun Ini
            @else {{ ucfirst($filter) }}
            @endif
            <br>
            Tanggal Cetak: {{ now()->format('d M Y, H:i') }}
        </p>
        <!-- Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nota ID</th>
                    <th>ID Produk</th>
                    <th>Nama Produk</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->notabeli?->id ?? '-' }}</td>
                        <td>{{ $purchase->produkbatches?->produks_id ?? '-' }}</td>
                        <td>{{ $purchase->produkbatches?->produks?->nama ?? '-' }}</td>
                        <td>{{ $purchase->quantity }} {{ $purchase->produkbatches?->satuan?->nama ?? $purchase->produkbatches?->satuans?->nama ?? '' }}</td>
                        <td>Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data pembelian untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total Pembelian</th>
                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
        <button onclick="window.print()" class="btn btn-primary mt-3">Print Laporan</button>
        <a href="{{ route('notabelis.csv', ['filter' => $filter]) }}" class="btn btn-success mt-3">Download CSV</a>
    </div>
@endsection
