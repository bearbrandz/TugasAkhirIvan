<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Models\Produkbatches;
use App\Models\HppRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncHppAverage extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hpp:sync
                            {--dry-run : Tampilkan hasil tanpa menyimpan perubahan}
                            {--product= : Sinkronisasi produk tertentu saja (by ID)}';

    /**
     * The console command description.
     */
    protected $description = 'Sinkronisasi hpp_avg_per_unit di semua batch aktif berdasarkan riwayat HPP Moving Average (hpp_records)';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $productId = $this->option('product');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║       SINKRONISASI HPP AVERAGE (MOVING AVERAGE)      ║');
        $this->info('╚══════════════════════════════════════════════════════╝');

        if ($isDryRun) {
            $this->warn('  Mode DRY-RUN aktif — tidak ada perubahan yang akan disimpan.');
        }

        $this->info('');

        // Ambil semua produk atau produk tertentu
        $query = Produk::withTrashed(false); // hanya produk aktif
        if ($productId) {
            $query->where('id', $productId);
        }
        $produks = $query->orderBy('nama')->get();

        if ($produks->isEmpty()) {
            $this->error('Tidak ada produk yang ditemukan.');
            return self::FAILURE;
        }

        $this->info("  Memproses {$produks->count()} produk...");
        $this->info('');

        $headers = ['Produk', 'HPP Lama (avg unitprice)', 'HPP Baru (Moving Avg)', 'Status', 'Batch Diupdate'];
        $rows = [];
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalNoRecord = 0;

        foreach ($produks as $produk) {
            /*
            |--------------------------------------------------------------------------
            | 1. Ambil HPP Moving Average terbaru dari hpp_records
            |--------------------------------------------------------------------------
            */
            $latestRecord = HppRecord::where('produks_id', $produk->id)
                ->orderBy('id', 'desc')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | 2. Ambil semua batch aktif produk ini
            |--------------------------------------------------------------------------
            */
            $activeBatches = Produkbatches::where('produks_id', $produk->id)
                ->where('status', 'tersedia')
                ->where(function ($q) {
                    $q->whereDate('tgl_kadaluarsa', '>', now())
                        ->orWhereNull('tgl_kadaluarsa');
                })
                ->get();

            if ($activeBatches->isEmpty()) {
                $rows[] = [
                    $produk->nama,
                    '-',
                    '-',
                    '⏭  Tidak ada batch aktif',
                    '0',
                ];
                $totalSkipped++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Tentukan nilai HPP yang akan digunakan
            | Prioritas:
            |   a. hpp_records terbaru (Moving Average historis — paling akurat)
            |   b. Hitung ulang WAC dari unitprice × stok batch aktif (fallback)
            |--------------------------------------------------------------------------
            */
            if ($latestRecord && (float) $latestRecord->hpp_avg_baru > 0) {
                $hppBaru = (float) $latestRecord->hpp_avg_baru;
                $sumber  = 'hpp_records';
            } else {
                // Fallback: hitung weighted average dari unitprice batch yang ada stok
                $totalNilai = $activeBatches->sum(fn($b) => (float) $b->unitprice * (int) $b->stok);
                $totalStok  = $activeBatches->sum(fn($b) => (int) $b->stok);
                $hppBaru    = $totalStok > 0 ? ($totalNilai / $totalStok) : 0;
                $sumber     = 'fallback (unitprice)';
                $totalNoRecord++;
            }

            if ($hppBaru <= 0) {
                $rows[] = [
                    $produk->nama,
                    '-',
                    '-',
                    '⚠  HPP tidak valid (0)',
                    '0',
                ];
                $totalSkipped++;
                continue;
            }

            // Hitung HPP lama (rata-rata hpp_avg_per_unit yang sudah ada, atau unitprice)
            $hppLama = $activeBatches
                ->filter(fn($b) => (float) ($b->hpp_avg_per_unit ?? 0) > 0)
                ->avg(fn($b) => (float) $b->hpp_avg_per_unit)
                ?? $activeBatches->avg(fn($b) => (float) $b->unitprice);

            $jumlahBatch = $activeBatches->count();

            /*
            |--------------------------------------------------------------------------
            | 4. Update hpp_avg_per_unit ke semua batch aktif
            |--------------------------------------------------------------------------
            */
            if (!$isDryRun) {
                DB::table('produkbatches')
                    ->where('produks_id', $produk->id)
                    ->where('status', 'tersedia')
                    ->where(function ($q) {
                        $q->whereDate('tgl_kadaluarsa', '>', now())
                            ->orWhereNull('tgl_kadaluarsa');
                    })
                    ->update(['hpp_avg_per_unit' => $hppBaru]);
            }

            $status = $isDryRun
                ? "🔍 Preview (dari {$sumber})"
                : "✅ Diupdate (dari {$sumber})";

            $rows[] = [
                $produk->nama,
                'Rp ' . number_format((float) $hppLama, 0, ',', '.'),
                'Rp ' . number_format($hppBaru, 0, ',', '.'),
                $status,
                (string) $jumlahBatch,
            ];

            $totalUpdated++;
        }

        $this->table($headers, $rows);

        $this->info('');
        $this->info('  Ringkasan:');
        $this->info("  ✅ Produk diproses  : {$totalUpdated}");
        $this->info("  ⚠  Fallback unitprice: {$totalNoRecord}");
        $this->info("  ⏭  Dilewati          : {$totalSkipped}");

        if (!$isDryRun) {
            $this->info('');
            $this->info('  ✔ Semua hpp_avg_per_unit berhasil disinkronisasi!');
        } else {
            $this->info('');
            $this->warn('  Ini hanya preview. Jalankan tanpa --dry-run untuk menyimpan perubahan.');
        }

        $this->info('');

        return self::SUCCESS;
    }
}
