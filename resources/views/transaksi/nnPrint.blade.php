<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Narkotika & Psikotropika - Apotek Medico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #000; background: #fff; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .header p { font-size: 14px; }
        .info { margin-bottom: 20px; font-size: 13px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        table th, table td { border: 1px solid #333; padding: 6px 8px; text-align: center; }
        table th { background-color: #f2f2f2; font-weight: bold; }
        .footer { text-align: center; font-size: 10px; color: #555; margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 10px; }
        @media print {
            body { padding: 0; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>APOTEK MEDICO</h2>
        <p>Laporan Penggunaan Obat Narkotika & Psikotropika</p>
    </div>

    <div class="info">
        Racikan ID: {{ $datas->first()->racikan_id ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Nama Obat</th>
                <th>Satuan</th>
                <th>Stok Awal</th>
                <th>Distributor</th>
                <th>Diterima</th>
                <th>Dipakai</th>
                <th>Stok Akhir</th>
                <th>Nama Pasien</th>
                <th>Alamat Pasien</th>
                <th>Nama Dokter</th>
                <th>Alamat Dokter</th>
                <th>Tanggal Ambil</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $d)
                <tr>
                    <td>{{ $d->batch_id }}</td>
                    <td>{{ $d->nama_produk }}</td>
                    <td>{{ $d->nama_satuan }}</td>
                    <td>{{ $d->stok_awalbulan }}</td>
                    <td>{{ $d->nama_distributor }}</td>
                    <td>{{ $d->stok_diterima }}</td>
                    <td>{{ $d->stok_keluar }}</td>
                    <td>{{ $d->stok_akhirbulan }}</td>
                    <td>{{ $d->nama_pasien }}</td>
                    <td>{{ $d->alamat_pasien }}</td>
                    <td>{{ $d->nama_dokter }}</td>
                    <td>{{ $d->alamat_dokter }}</td>
                    <td>{{ $d->tgl_ambil }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} &mdash; Apotek Medico
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
