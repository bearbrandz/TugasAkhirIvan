<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CountProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung total obat berdasarkan golongan di database live';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("========================================");
        $this->info("  JUMLAH OBAT DI DATABASE DOMAINESIA");
        $this->info("========================================");

        $totalSemua = DB::table('produks')->count();
        $totalAktif = DB::table('produks')->whereNull('deleted_at')->count();

        $counts = DB::table('produks')
            ->select('golongan', DB::raw('count(*) as total'))
            ->whereNull('deleted_at')
            ->groupBy('golongan')
            ->get();

        foreach ($counts as $c) {
            $golongan = $c->golongan === null ? 'Tidak Ada Golongan' : ($c->golongan === '' ? 'Kosong' : ucfirst($c->golongan));
            $this->line("- {$golongan} : {$c->total} obat");
        }

        $this->info("----------------------------------------");
        $this->info("Total Obat Aktif: {$totalAktif}");
        $this->info("Total Keseluruhan (termasuk dihapus/arsip): {$totalSemua}");
        $this->info("========================================");
    }
}
