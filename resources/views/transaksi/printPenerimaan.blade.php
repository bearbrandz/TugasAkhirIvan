<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penerimaan #{{ $data->batch_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 32px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
        }

        .header p {
            margin: 4px 0 0;
            color: #4b5563;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .items th,
        .items td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        .items th {
            background: #f3f4f6;
        }

        .footer {
            margin-top: 48px;
            display: flex;
            justify-content: space-between;
        }

        .sign {
            width: 220px;
            text-align: center;
        }

        .sign-space {
            height: 72px;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

    <button onclick="window.print()">Print</button>

    <div class="header">
        <h2>NOTA PENERIMAAN BARANG</h2>
        <p>Sistem Informasi Apotek</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="180">No Penerimaan</td>
                <td>: PN-{{ str_pad($data->batch_id, 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td>ID Batch</td>
                <td>: {{ $data->batch_id }}</td>
            </tr>
            <tr>
                <td>Nota Pembelian</td>
                <td>: {{ $data->nota_beli_id ? 'NB-' . str_pad($data->nota_beli_id, 5, '0', STR_PAD_LEFT) : '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Terima</td>
                <td>: {{ $data->tgl_datang ? \Carbon\Carbon::parse($data->tgl_datang)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Pegawai</td>
                <td>: {{ $data->nama_pegawai ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Distributor</th>
                <th>Gudang</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Harga Beli</th>
                <th>Tgl Produksi</th>
                <th>Tgl Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $data->nama_produk }}</td>
                <td>{{ $data->nama_distributor ?? '-' }}</td>
                <td>{{ $data->nama_gudang ?? '-' }}</td>
                <td>{{ number_format($data->stok, 0, ',', '.') }}</td>
                <td>{{ $data->nama_satuan ?? '-' }}</td>
                <td>Rp {{ number_format($data->unitprice, 0, ',', '.') }}</td>
                <td>{{ $data->tgl_produksi ? \Carbon\Carbon::parse($data->tgl_produksi)->format('d/m/Y') : '-' }}</td>
                <td>{{ $data->tgl_kadaluarsa ? \Carbon\Carbon::parse($data->tgl_kadaluarsa)->format('d/m/Y') : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="sign">
            <div>Penerima</div>
            <div class="sign-space"></div>
            <div>{{ $data->nama_pegawai ?? '(........................)' }}</div>
        </div>

        <div class="sign">
            <div>Mengetahui</div>
            <div class="sign-space"></div>
            <div>(........................)</div>
        </div>
    </div>

</body>
</html>