@extends('layout.conquer')

@section('title')

@section('content')
    <style>

        :root {
            --bg-color: #0f172a;
            --text-color: #e5e7eb;
            --border-color: #374151;
            --th-bg: #1e293b;
            --th-text: #f8fafc;
            --stripe-bg: #162033;
            --muted-text: #9ca3af;
            --summary-bg: #1e293b;
            --brand-color: var(--th-text);
        }

        @media print {
            :root {
                --bg-color: var(--th-text);
                --text-color: var(--text-color);
                --border-color: #d1d5db;
                --th-bg: #111827;
                --th-text: #ffffff;
                --stripe-bg: #f9fafb;
                --muted-text: #6b7280;
                --summary-bg: #f3f4f6;
                --brand-color: var(--text-color);
            }
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

    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Detail Nota Kadaluarsa</h1>

    @php
        // Group items by distributor ID
        $groupedByDistributor = $produk->groupBy(function ($item) {
            return $item->produkbatches->distributors_id;
        });
    @endphp
    <h2>Nota Kadaluarsa</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>Satuan</th>
                <th>HPP</th>
                <th>Total Harga</th>
                <th>Tanggal Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $nota->batch_id }}</td>
                <td>{{ $nota->nama_produk }}</td>
                <td>{{ $nota->stok }}</td>
                <td>{{ $nota->nama_satuan }}</td>
                <td>Rp {{ number_format($nota->hpp, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($nota->total_harga, 0, ',', '.') }}</td>
                <td>{{ $nota->tgl_kadaluarsa }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <button onclick="window.print()" class="btn btn-primary mt-3">Print</button>
    </div>

    <style>

        :root {
            --bg-color: #0f172a;
            --text-color: #e5e7eb;
            --border-color: #374151;
            --th-bg: #1e293b;
            --th-text: #f8fafc;
            --stripe-bg: #162033;
            --muted-text: #9ca3af;
            --summary-bg: #1e293b;
            --brand-color: var(--th-text);
        }

        @media print {
            :root {
                --bg-color: var(--th-text);
                --text-color: var(--text-color);
                --border-color: #d1d5db;
                --th-bg: #111827;
                --th-text: #ffffff;
                --stripe-bg: #f9fafb;
                --muted-text: #6b7280;
                --summary-bg: #f3f4f6;
                --brand-color: var(--text-color);
            }
        }

        @media print {
            .btn {
                display: none;
            }

            .print-split {
                page-break-before: always;
            }
        }
    </style>
@endsection
