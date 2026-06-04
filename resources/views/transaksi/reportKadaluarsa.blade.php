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

        <h1>Laporan kadaluarsa</h1>
        <!-- Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Batch</th>
                    <th>ID Produk</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Stok</th>
                    <th>Satuan Batch</th>
                    <th>HPP Produk</th>
                    <th>Total Harga Produk</th>
                    <th>Tanggal Kadaluarsa Produk</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expires as $expire)
                    <tr>
                    <tr>
                        <td>{{ $expire->batch_id }}</td>
                        <td>{{ $expire->produk_id }}</td>
                        <td>{{ $expire->nama_produk }}</td>
                        <td>{{ $expire->stok }}</td>
                        <td>{{ $expire->nama_satuan }}</td>
                        <td>Rp {{ number_format($expire->hpp, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($expire->total_harga, 0, ',', '.') }}</td>
                        <td>{{ $expire->tgl_kadaluarsa }}</td>
                    </tr>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">Total Harga Semua Produk</th>
                    <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
        <button onclick="window.print()" class="btn btn-primary mt-3">Print Laporan</button>
        <a href="{{ route('produks.csvKadaluarsa', ['filter' => $filter]) }}" class="btn btn-success mt-3">Download CSV</a>
    </div>
@endsection
