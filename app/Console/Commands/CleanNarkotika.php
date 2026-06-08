<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Notajual;

class CleanNarkotika extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-narkotika';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan transaksi obat Narkotika/Psikotropika yang dijual via reguler dan memulihkan stok';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sql = "
            SELECT nj.id AS notajual_id
            FROM notajuals_has_produks njp
            INNER JOIN notajuals nj ON nj.id = njp.notajuals_id
            INNER JOIN produkbatches pb ON pb.id = njp.produkbatches_id
            INNER JOIN produks p ON p.id = pb.produks_id
            LEFT JOIN (
                SELECT notajuals_id, MAX(racikans_id) as racikans_id 
                FROM notajuals_has_racikans 
                GROUP BY notajuals_id
            ) njr ON njr.notajuals_id = nj.id
            WHERE p.golongan IN ('narkotika', 'psikotropika')
              AND nj.deleted_at IS NULL
              AND njp.deleted_at IS NULL
              AND njr.racikans_id IS NULL
        ";
        
        $data = DB::select($sql);
        $count = 0;

        foreach ($data as $d) {
            $id = $d->notajual_id;
            $nota = Notajual::find($id);
            if ($nota) {
                // Revert stock
                foreach ($nota->notaJualProduks as $item) {
                    $batch = $item->produkbatches;
                    if ($batch) {
                        $batch->increment('stok', $item->quantity);
                    }
                }
                
                // Soft delete pivot
                DB::table('notajuals_has_produks')
                    ->where('notajuals_id', $id)
                    ->update(['deleted_at' => now()]);
                    
                // Soft delete nota
                $nota->delete();
                $this->info("Berhasil menghapus Nota Jual #{$id} dan mengembalikan stok obat.");
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("Selesai! Berhasil membersihkan {$count} transaksi yang tidak sesuai aturan.");
        } else {
            $this->info("Aman! Tidak ada transaksi Narkotika/Psikotropika yang melanggar aturan.");
        }
    }
}
