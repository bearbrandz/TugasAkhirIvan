<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan #{{ $nota->id ?? '-' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #ffffff;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .nota-wrapper {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .print-actions {
            margin-bottom: 16px;
            text-align: right;
        }

        .btn-print {
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            background: #ef4444;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }

        .nota-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            border-bottom: 2px solid #111827;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }

        .brand p {
            margin: 4px 0 0;
            color: #6b7280;
        }

        .nota-title {
            text-align: right;
        }

        .nota-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
        }

        .nota-title p {
            margin: 4px 0 0;
            color: #4b5563;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .info-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
        }

        .info-row {
            display: flex;
            margin-bottom: 6px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            width: 130px;
            color: #6b7280;
        }

        .info-value {
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th {
            background: #111827;
            color: #ffffff;
            border: 1px solid #111827;
            padding: 9px 8px;
            text-align: left;
            font-size: 12px;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 9px 8px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .product-meta {
            display: block;
            color: #6b7280;
            font-size: 11px;
            margin-top: 3px;
            line-height: 1.35;
        }

        .summary-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .summary-table {
            width: 360px;
            margin-bottom: 0;
        }

        .summary-table td {
            padding: 8px 10px;
        }

        .summary-table tr:last-child td {
            font-weight: 800;
            font-size: 15px;
            border-top: 2px solid #111827;
            background: #f3f4f6;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
        }

        .signature {
            width: 220px;
            text-align: center;
        }

        .signature-space {
            height: 70px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .print-actions {
                display: none !important;
            }

            .nota-wrapper {
                max-width: none;
                width: 100%;
                padding: 10mm;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>

<body>
@php
    $produkItems = ($nota->notajualproduks ?? collect())->groupBy(function($item) {
        return $item->produkbatches?->produks_id ?? $item->id;
    })->map(function($group) {
        $first = $group->first();
        return (object) [
            'nama_produk' => $first->produkbatches?->produks?->nama ?? '-',
            'quantity' => $group->sum('quantity'),
            'subtotal' => $group->sum('subtotal'),
            'batch_ids' => $group->pluck('produkbatches.id')->filter()->unique()->implode(', '),
            'satuan' => $first->produkbatches?->satuan?->nama ?? $first->produkbatches?->satuans?->nama ?? ''
        ];
    })->values();

    $racikanItems = $nota->notajualracikans ?? collect();

    $grandTotal = 0;

    foreach ($produkItems as $item) {
        $grandTotal += (float) ($item->subtotal ?? 0);
    }

    foreach ($racikanItems as $item) {
        $racikan = $item->racikan ?? null;
        $grandTotal += (float) ($racikan->biaya_embalase ?? 0);
    }

    $totalBelanja = (float) ($nota->total_bayar ?? $grandTotal);
    if ($totalBelanja <= 0) {
        $totalBelanja = $grandTotal;
    }

    $nominalBayar = (float) ($nota->nominal_bayar ?? $totalBelanja);
    if ($nominalBayar <= 0) {
        $nominalBayar = $totalBelanja;
    }

    $kembalian = (float) ($nota->kembalian ?? max(0, $nominalBayar - $totalBelanja));
    $metodeBayar = $nota->metode_bayar ?? 'tunai';

    $tanggalNota = $nota->created_at
        ? \Carbon\Carbon::parse($nota->created_at)->format('d F Y H:i')
        : '-';

    $namaPegawai = $nota->user->nama ?? '-';
@endphp

<div class="nota-wrapper">
    <div class="print-actions">
        <button type="button" onclick="window.print()" class="btn-print">
            Print Nota
        </button>
    </div>

    <div class="nota-header">
        <div class="brand">
            <h1>Apotek Medico</h1>
            <p>Sistem Informasi Apotek</p>
        </div>

        <div class="nota-title">
            <h2>Nota Penjualan</h2>
            <p>
                No Nota:
                <strong>
                    {{ $nota->nomor_nota ?? 'NJ-' . str_pad($nota->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                </strong>
            </p>
            <p>Tanggal: {{ $tanggalNota }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-row">
                <div class="info-label">Nota ID</div>
                <div class="info-value">#{{ $nota->id ?? '-' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Pegawai</div>
                <div class="info-value">{{ $namaPegawai }}</div>
            </div>
        </div>

        <div class="info-box">
            <div class="info-row">
                <div class="info-label">Jumlah Item</div>
                <div class="info-value">
                    {{ $produkItems->count() + $racikanItems->count() }}
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Metode Bayar</div>
                <div class="info-value">{{ ucfirst($metodeBayar) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Produk</th>
                <th width="90" class="text-right">Qty</th>
                <th width="130" class="text-right">Harga Satuan</th>
                <th width="140" class="text-right">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp

            @forelse ($produkItems as $item)
                @php
                    $namaProduk = $item->nama_produk;
                    $qty = (float) $item->quantity;
                    $subtotal = (float) $item->subtotal;
                    $hargaSatuan = $qty > 0 ? ($subtotal / $qty) : 0;
                    $batchId = $item->batch_ids;
                    $satuan = $item->satuan;
                @endphp

                <tr>
                    <td class="text-center">{{ $no++ }}</td>

                    <td>
                        <strong>{{ $namaProduk }}</strong>
                        <span class="product-meta">
                            Batch: #{{ $batchId }}
                            @if($satuan)
                                <br>Satuan: {{ $satuan }}
                            @endif
                        </span>
                    </td>

                    <td class="text-right">
                        {{ number_format($qty, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                @if($racikanItems->count() === 0)
                    <tr>
                        <td colspan="5" class="text-center">
                            Tidak ada item penjualan.
                        </td>
                    </tr>
                @endif
            @endforelse

            @foreach ($racikanItems as $item)
                @php
                    $racikan = $item->racikan;
                    $biaya = (float) ($racikan->biaya_embalase ?? 0);
                @endphp

                <tr>
                    <td class="text-center">{{ $no++ }}</td>

                    <td>
                        <strong>Biaya Embalase - {{ $racikan->nama ?? 'Racikan' }}</strong>
                        <span class="product-meta">
                            Aturan Pakai: {{ $racikan->aturan_pakai ?? '-' }}
                        </span>
                    </td>

                    <td class="text-right">
                        {{ number_format($item->quantity ?? 1, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($biaya, 0, ',', '.') }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($biaya, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary-table">
            <tr>
                <td>Total Belanja</td>
                <td class="text-right">
                    Rp {{ number_format($totalBelanja, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td>Metode Bayar</td>
                <td class="text-right">
                    {{ ucfirst($metodeBayar) }}
                </td>
            </tr>

            <tr>
                <td>Dibayar Pembeli</td>
                <td class="text-right">
                    Rp {{ number_format($nominalBayar, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td>Kembalian</td>
                <td class="text-right">
                    Rp {{ number_format($kembalian, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div>
            <strong>Catatan:</strong>
            <br>
            Nota ini dicetak dari Sistem Informasi Apotek.
        </div>

        <div class="signature">
            <div>Petugas</div>
            <div class="signature-space"></div>
            <div>
                <strong>{{ $namaPegawai ?: '________________' }}</strong>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300);
    });
</script>
</body>
</html>