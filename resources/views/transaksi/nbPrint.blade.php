<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pembelian #{{ $nota->id ?? '-' }}</title>

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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: var(--bg-color);
            color: var(--text-color);
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
            color: var(--th-text);
            font-weight: 700;
            cursor: pointer;
        }

        .nota-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            border-bottom: 2px solid var(--brand-color);
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
            color: var(--muted-text);
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
            color: var(--muted-text);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .info-box {
            border: 1px solid var(--border-color);
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
            color: var(--muted-text);
        }

        .info-value {
            font-weight: 700;
        }

        .distributor-title {
            margin: 22px 0 8px;
            font-size: 15px;
            font-weight: 800;
            color: var(--text-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th {
            background: var(--th-bg);
            color: var(--th-text);
            border: 1px solid var(--border-color);
            padding: 9px 8px;
            text-align: left;
            font-size: 12px;
        }

        td {
            border: 1px solid var(--border-color);
            padding: 9px 8px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: var(--stripe-bg);
        }

        tfoot td {
            background: var(--summary-bg);
            font-weight: 800;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .product-meta {
            display: block;
            color: var(--muted-text);
            font-size: 11px;
            margin-top: 3px;
            line-height: 1.35;
        }

        .grand-total-box {
            margin-top: 12px;
            border: 2px solid var(--brand-color);
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 800;
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
    /*
        Support 2 kemungkinan data:
        1. Controller baru mengirim $items dari query DB.
        2. Controller lama hanya mengirim $nota dengan relasi notabeliproduks.
    */
    $printItems = collect($items ?? []);

    if ($printItems->isEmpty() && isset($nota->notabeliproduks)) {
        $printItems = $nota->notabeliproduks->map(function ($item) {
            $batch = $item->produkbatches ?? null;

            return (object) [
                'notabelis_id'      => $item->notabelis_id ?? null,
                'produkbatches_id'  => $item->produkbatches_id ?? null,
                'quantity'          => $item->quantity ?? 0,
                'subtotal'          => $item->subtotal ?? 0,
                'batch_id'          => $batch->id ?? null,
                'produks_id'        => $batch->produks_id ?? null,
                'unitprice'         => $batch->unitprice ?? 0,
                'tgl_produksi'      => $batch->tgl_produksi ?? null,
                'tgl_kadaluarsa'    => $batch->tgl_kadaluarsa ?? null,
                'nama_produk'       => $batch->produks->nama ?? '-',
                'nama_distributor'  => $batch->distributor->nama ?? $batch->distributors->nama ?? 'Distributor Tidak Diketahui',
                'nama_satuan'       => $batch->satuan->nama ?? $batch->satuans->nama ?? '-',
            ];
        });
    }

    $totalNota = $total ?? $printItems->sum('subtotal');

    $tanggalNota = $nota->created_at ?? null;
    $namaPegawai = $nota->nama_pegawai ?? $nota->user->nama ?? '-';

    $groupedByDistributor = $printItems->groupBy(function ($item) {
        return $item->nama_distributor ?? 'Distributor Tidak Diketahui';
    });
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
            <h2>Nota Pembelian</h2>
            <p>
                No Nota:
                <strong>
                    {{ $nota->nomor_nota ?? 'NB-' . str_pad($nota->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                </strong>
            </p>
            <p>
                Tanggal:
                {{ $tanggalNota ? \Carbon\Carbon::parse($tanggalNota)->format('d F Y H:i') : '-' }}
            </p>
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
                <div class="info-label">Total Qty</div>
                <div class="info-value">
                    {{ number_format($printItems->sum('quantity'), 0, ',', '.') }}
                    {{ $printItems->first()->nama_satuan ?? '' }}
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Total Pembelian</div>
                <div class="info-value">Rp {{ number_format($totalNota, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @forelse ($groupedByDistributor as $distributorName => $itemsGroup)
        @php
            $totalDistributor = $itemsGroup->sum('subtotal');
        @endphp

        <div class="distributor-title">
            Distributor: {{ $distributorName ?: 'Distributor Tidak Diketahui' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Produk</th>
                    <th width="90">Batch</th>
                    <th width="90">Satuan</th>
                    <th width="80" class="text-right">Qty</th>
                    <th width="120" class="text-right">Harga/Unit</th>
                    <th width="130" class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($itemsGroup as $i => $item)
                    @php
                        $qty = (float) ($item->quantity ?? 0);
                        $subtotal = (float) ($item->subtotal ?? 0);
                        $hargaUnit = $qty > 0 ? ($subtotal / $qty) : ($item->unitprice ?? 0);
                    @endphp

                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>

                        <td>
                            <strong>{{ $item->nama_produk ?? '-' }}</strong>
                            <span class="product-meta">
                                Produk ID: {{ $item->produks_id ?? '-' }}
                                <br>
                                Exp:
                                {{ !empty($item->tgl_kadaluarsa) ? \Carbon\Carbon::parse($item->tgl_kadaluarsa)->format('d/m/Y') : '-' }}
                            </span>
                        </td>

                        <td>#{{ $item->batch_id ?? $item->produkbatches_id ?? '-' }}</td>

                        <td>{{ $item->nama_satuan ?? '-' }}</td>

                        <td class="text-right">
                            {{ number_format($qty, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($hargaUnit, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">
                        Total Distributor
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($totalDistributor, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @empty
        <table>
            <tbody>
                <tr>
                    <td class="text-center">
                        Tidak ada item pembelian pada nota ini.
                    </td>
                </tr>
            </tbody>
        </table>
    @endforelse

    <div class="grand-total-box">
        <span>Total Pembelian</span>
        <span>Rp {{ number_format($totalNota, 0, ',', '.') }}</span>
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