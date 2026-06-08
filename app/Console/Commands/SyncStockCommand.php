<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Produkbatches;

class SyncStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan sinkronisasi dan audit ulang stok semua batch produk secara otomatis (Beli - Jual - Retur)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi dan audit stok...');
        
        $batches = Produkbatches::all();
        $correctedCount = 0;

        foreach ($batches as $b) {
            $beli = DB::table('notabelis_has_produks')->where('produkbatches_id', $b->id)->sum('quantity');
            
            $jual = DB::table('notajuals_has_produks')
                ->join('notajuals', 'notajuals.id', '=', 'notajuals_has_produks.notajuals_id')
                ->where('notajuals_has_produks.produkbatches_id', $b->id)
                ->whereNull('notajuals.deleted_at')
                ->sum('notajuals_has_produks.quantity');
                
            $returBeli = DB::table('retur_pembelian_details')
                ->join('retur_pembelians', 'retur_pembelians.id', '=', 'retur_pembelian_details.retur_pembelian_id')
                ->where('retur_pembelian_details.produkbatches_id', $b->id)
                ->whereNull('retur_pembelians.deleted_at')
                ->sum('retur_pembelian_details.qty');
                
            $correctStok = $beli - $jual - $returBeli;
            
            if ($b->stok != $correctStok) {
                $this->warn("Batch {$b->id} (Produk {$b->produks_id}): Stok DB {$b->stok} diperbaiki menjadi {$correctStok}");
                $b->stok = $correctStok;
                $b->save();
                $correctedCount++;
            }
        }

        if ($correctedCount > 0) {
            $this->info("Berhasil melakukan sinkronisasi! Terdapat {$correctedCount} batch yang diperbaiki.");
        } else {
            $this->info("Semua stok di sistem sudah 100% akurat dan tersinkronisasi. Tidak ada perubahan yang dilakukan.");
        }
    }
}
