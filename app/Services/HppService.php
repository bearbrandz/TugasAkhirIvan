<?php

namespace App\Services;

use App\Models\HppRecord;
use App\Models\Produkbatches;

class HppService
{
    /**
     * Hitung ulang HPP Weighted Average (Moving Average) setelah pembelian / retur pembelian.
     *
     * Rumus:
     *   HPP Baru = (Nilai Stok Lama + Nilai Pembelian Baru) / (Stok Lama + Stok Baru)
     *
     * Di mana:
     *   Nilai Stok Lama = stok_lama × hpp_avg_lama
     *   Nilai Pembelian = stok_baru × harga_beli_per_unit
     */
    public static function hitungUlang(
        int $produksId,
        int $stokBaru,
        float $hargaBaru,
        string $tipe = 'pembelian',
        ?int $notabelisId = null,
        ?int $produkbatchesId = null
    ): float {
        /*
         * Ambil stok dan nilai persediaan yang sudah ada (sebelum transaksi ini).
         * Gunakan hpp_avg_per_unit sebagai HPP, fallback ke unitprice jika 0.
         */
        $existing = Produkbatches::where('produks_id', $produksId)
            ->where('status', 'tersedia')
            ->where(function ($q) {
                $q->whereDate('tgl_kadaluarsa', '>', now())
                    ->orWhereNull('tgl_kadaluarsa');
            })
            ->selectRaw('
                COALESCE(SUM(stok), 0) as total_stok,
                COALESCE(SUM(COALESCE(NULLIF(hpp_avg_per_unit, 0), unitprice, 0) * stok), 0) as total_nilai
            ')
            ->first();

        $stokLama  = (float) ($existing->total_stok ?? 0);
        $nilaiLama = (float) ($existing->total_nilai ?? 0);
        $hargaLama = $stokLama > 0 ? ($nilaiLama / $stokLama) : 0;

        $qtyTransaksi = abs($stokBaru);

        if ($tipe === 'retur') {
            /*
             * Retur pembelian mengurangi stok.
             * Hitung ulang HPP: (Nilai Persediaan Lama - Nilai Retur) / Stok Tersisa
             */
            $totalStokSetelah = max(0, $stokLama - $qtyTransaksi);
            $nilaiRetur = $qtyTransaksi * $hargaBaru;
            $nilaiSetelah = $nilaiLama - $nilaiRetur;

            if ($totalStokSetelah <= 0) {
                $hppBaru = 0;
            } else {
                $hppBaru = $nilaiSetelah / $totalStokSetelah;
                // Cegah HPP negatif karena selisih pembulatan
                if ($hppBaru < 0) {
                    $hppBaru = 0;
                }
            }
            $stokDicatat = -1 * $qtyTransaksi;
        } else {
            /*
             * Pembelian menambah stok.
             * Rumus Moving Average / Weighted Average:
             *   HPP Baru = (Nilai Lama + Nilai Baru) / (Stok Lama + Stok Baru)
             */
            $totalStokSetelah = $stokLama + $qtyTransaksi;

            if ($totalStokSetelah <= 0) {
                $hppBaru = $hargaBaru;
            } else {
                $hppBaru = (($stokLama * $hargaLama) + ($qtyTransaksi * $hargaBaru)) / $totalStokSetelah;
            }

            $stokDicatat = $qtyTransaksi;
        }

        HppRecord::create([
            'produkbatches_id' => $produkbatchesId,
            'produks_id'       => $produksId,
            'stok_lama'        => $stokLama,
            'harga_lama'       => $hargaLama,
            'stok_baru'        => $stokDicatat,
            'harga_baru'       => $hargaBaru,
            'hpp_avg_baru'     => $hppBaru,
            'tipe'             => $tipe,
            'notabelis_id'     => $notabelisId,
        ]);

        return (float) $hppBaru;
    }

    /**
     * Ambil HPP rata-rata terkini untuk produk.
     *
     * Prioritas:
     *   1. Hitung langsung dari batch aktif (paling akurat, mencerminkan kondisi nyata)
     *   2. Fallback ke hpp_records terakhir jika tidak ada batch aktif
     *   3. Fallback ke 0 jika tidak ada data sama sekali
     */
    public static function getHppTerkini(int $produksId): float
    {
        /*
         * Hitung HPP weighted average langsung dari batch aktif.
         * Ini paling akurat karena mencerminkan nilai persediaan aktual.
         */
        $hppDariBatch = self::hitungDariBatchAktif($produksId);

        if ($hppDariBatch > 0) {
            return $hppDariBatch;
        }

        /*
         * Fallback: ambil dari hpp_records terbaru.
         */
        $record = HppRecord::where('produks_id', $produksId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($record && (float) $record->hpp_avg_baru > 0) {
            return (float) $record->hpp_avg_baru;
        }

        return 0;
    }

    /**
     * Hitung HPP Weighted Average langsung dari batch aktif yang ada di DB.
     *
     * Rumus: SUM(hpp_avg_per_unit × stok) / SUM(stok)
     * Fallback: jika hpp_avg_per_unit = 0, gunakan unitprice.
     */
    public static function hitungDariBatchAktif(int $produksId): float
    {
        $result = Produkbatches::where('produks_id', $produksId)
            ->where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->where(function ($q) {
                $q->whereDate('tgl_kadaluarsa', '>', now())
                    ->orWhereNull('tgl_kadaluarsa');
            })
            ->selectRaw('
                SUM(COALESCE(NULLIF(hpp_avg_per_unit, 0), unitprice, 0) * stok)
                / NULLIF(SUM(stok), 0) as hpp_avg
            ')
            ->value('hpp_avg');

        return (float) ($result ?? 0);
    }

    /**
     * Update HPP average (hpp_avg_per_unit) ke semua batch aktif produk.
     * Dipanggil setelah setiap pembelian/retur untuk menjaga sinkronisasi.
     */
    public static function updateBatchHpp(int $produksId, float $hppBaru): void
    {
        if ($hppBaru <= 0) {
            return; // Jangan update jika HPP tidak valid
        }

        Produkbatches::where('produks_id', $produksId)
            ->where('status', 'tersedia')
            ->update([
                'hpp_avg_per_unit' => $hppBaru,
            ]);
    }
}