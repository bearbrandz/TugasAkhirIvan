<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Retur {{ $retur->no_retur ?: '#' . $retur->id }} - Apotek Medico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; background: #fff; }
        .container { max-width: 700px; margin: 20px auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 14px; }
        .header h2 { font-size: 16px; font-weight: bold; }
        .header p  { font-size: 11px; }
        .section-title { font-weight: bold; border-bottom: 1px dashed #000; padding-bottom: 4px; margin: 12px 0 8px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table th, table td { border: 1px solid #999; padding: 5px 8px; font-size: 11px; }
        table th { background: #eee; font-weight: bold; }
        .total-row td { font-weight: bold; background: #f5f5f5; }
        .footer { margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; text-align: center; font-size: 10px; color: #555; }
        .sig-box { display: flex; justify-content: space-between; margin-top: 30px; }
        .sig-box div { text-align: center; width: 45%; }
        .sig-box .sig-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 4px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>APOTEK MEDICO</h2>
            <p>Nota Retur Pembelian</p>
        </div>

        <div class="section-title">Informasi Retur</div>
        <div class="info-row"><span>No. Retur</span><span>{{ $retur->no_retur ?: '#' . $retur->id }}</span></div>
        <div class="info-row"><span>Tanggal Retur</span><span>{{ \Carbon\Carbon::parse($retur->tanggal_retur)->format('d/m/Y') }}</span></div>
        <div class="info-row"><span>Nota Beli Asal</span><span>#{{ $retur->notabelis_id }}</span></div>
        @if($retur->keterangan)
            <div class="info-row"><span>Keterangan</span><span>{{ $retur->keterangan }}</span></div>
        @endif

        <!-- Removed distributor section: return now links directly to products without batch/distributor info -->

        <div class="section-title">Daftar Barang Retur</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga/Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($retur->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->produk->nama ?? '-' }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" style="text-align:right;">TOTAL RETUR</td>
                    <td>Rp {{ number_format($retur->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature section removed as we no longer track pegawai or distributor on returns -->

        <div class="footer">
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} &mdash; Apotek Medico &mdash; Sistem Informasi Apotek
        </div>
    </div>
    <script>window.onload = function(){ window.print(); }</script>
</body>
</html>
