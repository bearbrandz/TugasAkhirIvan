<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Apotek Medico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; background: #fff; padding: 20px; }
        
        /* HEADER */
        .print-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-box { color: #2c3e50; padding: 10px 0px; display: inline-block; font-weight: bold; font-size: 24px; }
        .logo-box span { color: #e74c3c; }
        .print-title { text-align: right; }
        .print-title h1 { font-size: 24px; color: #2c3e50; margin-bottom: 5px; }
        .print-title p { font-size: 14px; color: #7f8c8d; }
        
        /* TABLE */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th, td { border: 1px solid #bdc3c7; padding: 10px; text-align: left; }
        th { background-color: #f2f6f8; font-weight: bold; color: #2c3e50; }
        
        /* FOOTER */
        .tfoot-total th { background-color: #ecf0f1; font-size: 14px; }
        .tfoot-total th:last-child { text-align: right; color: #27ae60; }
        td:last-child { text-align: right; }
        
        /* ACTION BUTTONS (Hidden on print) */
        .actions { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 8px 16px; margin-right: 10px; text-decoration: none; border-radius: 4px; font-size: 14px; cursor: pointer; border: none; font-weight: bold; }
        .btn-primary { background: #3498db; color: #fff; }
        .btn-success { background: #2ecc71; color: #fff; }
        .btn-secondary { background: #95a5a6; color: #fff; }
        
        @media print {
            .no-print, .actions { display: none !important; }
            body { padding: 0; background: #fff; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="actions no-print">
        <button onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
        <button onclick="window.print()" class="btn btn-primary">Print Laporan</button>
        <a href="{{ route('notajuals.csv', ['filter' => $filter]) }}" class="btn btn-success">Download CSV</a>
    </div>

    <div class="print-header">
        <div class="logo-box">
            APOTEK<br>
            <span style="border: 2px solid #e74c3c; padding: 0 3px; margin-right: 1px; display: inline-block; line-height: 0.9;">M</span><span style="color: #e74c3c;">edico</span>
        </div>
        <div class="print-title">
            <h1>Laporan Penjualan</h1>
            <p>
                Filter: 
                @if($filter == 'day') Hari Ini
                @elseif($filter == 'week') Minggu Ini
                @elseif($filter == 'month') Bulan Ini
                @elseif($filter == 'year') Tahun Ini
                @endif
                <br>
                Tanggal Cetak: {{ now()->format('d M Y, H:i') }}
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Nota ID</th>
                <th width="15%">ID Produk</th>
                <th width="40%">Nama Produk</th>
                <th width="15%">Quantity</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->notajual_id ?? '-' }}</td>
                    <td>{{ $sale->produk_id ?? '-' }}</td>
                    <td>{{ $sale->nama_produk ?? '-' }}</td>
                    <td>{{ $sale->quantity }} {{ $sale->nama_satuan ?? '' }}</td>
                    <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada data penjualan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <th colspan="4" style="text-align: right;">Total Penjualan:</th>
                <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; text-align: right; font-size: 14px;">
        <p>Mengetahui,</p>
        <br><br><br>
        <p><strong>{{ auth()->user()->nama ?? 'Admin/Kasir' }}</strong></p>
        <p>Apotek Medico</p>
    </div>

    <script>
        // Optional: Auto trigger print when page loads
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
