<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddGudangCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-gudang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan lokasi gudang Alkes untuk menyimpan barang berat seperti oksigen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Mengecek ketersediaan Gudang Alkes...");

        $exists = DB::table('gudangs')->where('lokasi', 'Gudang Belakang (Alkes & Barang Besar)')->first();

        if (!$exists) {
            DB::table('gudangs')->insert([
                'lokasi' => 'Gudang Belakang (Alkes & Barang Besar)',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info("Berhasil! 'Gudang Belakang (Alkes & Barang Besar)' telah ditambahkan ke sistem.");
        } else {
            $this->info("Gudang tersebut sudah ada di dalam sistem.");
        }
    }
}
