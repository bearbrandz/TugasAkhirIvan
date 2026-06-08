<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memundurkan tanggal stok awal ke 25 Mei agar tidak minus di laporan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memperbaiki paradoks waktu pada database...");

        DB::table('notabelis')->whereDate('created_at', '>=', '2026-05-27')
            ->update(['created_at' => '2026-05-25 08:00:00', 'updated_at' => '2026-05-25 08:00:00']);

        DB::table('notabelis_has_produks')->whereDate('created_at', '>=', '2026-05-27')
            ->update(['created_at' => '2026-05-25 08:00:00', 'updated_at' => '2026-05-25 08:00:00']);

        DB::table('produkbatches')->whereDate('created_at', '>=', '2026-05-27')
            ->update(['created_at' => '2026-05-25 08:00:00', 'updated_at' => '2026-05-25 08:00:00']);

        DB::table('hpp_records')->whereDate('created_at', '>=', '2026-05-27')
            ->update(['created_at' => '2026-05-25 08:00:00', 'updated_at' => '2026-05-25 08:00:00']);

        $this->info("Berhasil! Semua stok awal sudah dipindah ke tanggal 25 Mei 2026.");
    }
}
