@extends('layout.conquer')

@section('content')
    <style>
        @media print {

            .page-sidebar-menu,
            .main-sidebar,
            .navbar,
            .footer,
            .page-sidebar-menu-collapse {
                display: none !important;
            }

            .content-wrapper,
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            body {
                overflow: visible !important;
            }

            button {
                display: none !important;
            }

            a {
                display: none !important;
            }
        }
    </style>

    <div class="container">

        <h1>Laporan Pembelian</h1>
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
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->notabeli->id ?? '-' }}</td>
                        <td>{{ $purchase->produkbatches->produks_id ?? '-' }}</td>
                        <td>{{ $purchase->produkbatches->produks->nama ?? '-' }}</td>
                        <td>{{ $purchase->quantity }}</td>
                        <td>Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Total Penjualan</th>
                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
        <button onclick="window.print()" class="btn btn-primary mt-3">Print Laporan</button>
        <a href="{{ route('notabelis.csv', ['filter' => $filter]) }}" class="btn btn-success mt-3">Download CSV</a>
    </div>
@endsection
