@extends('layout.conquer')

@section('title')

@section('content')
    <style>

        :root {
            --bg-color: #ffffff;
            --text-color: #111827;
            --border-color: #d1d5db;
            --th-bg: #f9fafb;
            --th-text: #111827;
            --stripe-bg: #f9fafb;
            --muted-text: #6b7280;
            --summary-bg: #f3f4f6;
            --brand-color: #111827;
            --title-color: #111827;
        }

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
        }
    </style>

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Detail Nota Penerimaan</h1>

    <div class="container mt-4">
        <h2>Nota Penerimaan</h2>
        <strong>No Nota:</strong>
        {{ $nota->nomor_nota ?? 'NJ-' . str_pad($nota->id, 5, '0', STR_PAD_LEFT) }}
        <p><strong>Tanggal:</strong> {{ $nota->created_at->format('d M Y') }}</p>
        <p><strong>Pegawai:</strong> {{ $nota->user->nama }}</p>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Batch ID</th>
                    <th>Stok Diterima</th>
                    <th>Lokasi Gudang</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @php
                        $namaProduk = optional(optional($nota->produkbatches)->produks)->nama ?? '-';
                        $lokasiGudang = optional($nota->gudangs)->lokasi ?? '-';
                    @endphp
                    <td>{{ $namaProduk }}</td>
                    <td>{{ $nota->produkbatches_id }}</td>
                    <td>{{ $nota->stok }}</td>
                    <td>{{ $lokasiGudang }}</td>
                </tr>
            </tbody>
        </table>

        <button onclick="window.print()" class="btn btn-primary mt-3">Print Nota</button>
    </div>
@endsection
