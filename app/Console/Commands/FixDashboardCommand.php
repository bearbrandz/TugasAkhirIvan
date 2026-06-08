<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class FixDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyuntikkan data pembelian bulan Juni agar grafik Pembelian dan Total Pembelian positif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Menyiapkan data pembelian bulan Juni untuk Dashboard...");

        $pegawais = DB::table('users')->whereIn('tipe_user', ['kasir', 'apoteker', 'admin'])->pluck('id')->toArray();
        $distributor = DB::table('distributors')->first();
        $gudang = DB::table('gudangs')->first();
        $satuan = DB::table('satuans')->first();

        if (!$distributor || !$gudang || !$satuan || empty($pegawais)) {
            $this->error("Data referensi (Distributor/Gudang/Satuan/Pegawai) tidak lengkap. Gagal membuat transaksi.");
            return;
        }

        $sat_id = $satuan->id;
        $dist_id = $distributor->id;
        $gud_id = $gudang->id;

        // Beli di awal Juni
        $beliTime = Carbon::create(2026, 6, 2, 10, 0, 0);

        $nb1_id = DB::table('notabelis')->insertGetId([
            'pegawai_id' => $pegawais[0],
            'created_at' => $beliTime,
            'updated_at' => $beliTime,
        ]);

        $produks = DB::table('produks')->whereNotIn('golongan', ['narkotika', 'psikotropika'])->inRandomOrder()->take(5)->get();

        foreach ($produks as $p) {
            $qty = rand(500, 1000);
            $harga = $p->hargabeli ?? rand(5000, 20000);
            $subtotal = $qty * $harga;
            
            $batchId = DB::table('produkbatches')->insertGetId([
                'produks_id' => $p->id,
                'satuans_id' => $p->satuans_id ?? $sat_id,
                'unitprice' => $harga,
                'stok' => $qty,
                'tgl_produksi' => $beliTime->copy()->subMonths(2)->toDateString(),
                'tgl_kadaluarsa' => $beliTime->copy()->addYears(2)->toDateString(),
                'status' => 'tersedia',
                'distributors_id' => $dist_id,
                'gudangs_id' => $gud_id,
                'hpp_avg_per_unit' => $harga,
                'created_at' => $beliTime,
                'updated_at' => $beliTime,
            ]);
            
            DB::table('notabelis_has_produks')->insert([
                'notabelis_id' => $nb1_id,
                'produkbatches_id' => $batchId,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'created_at' => $beliTime,
                'updated_at' => $beliTime,
            ]);
        }

        // Beli di pertengahan Juni
        $beliTime2 = Carbon::create(2026, 6, 7, 14, 0, 0);

        $nb2_id = DB::table('notabelis')->insertGetId([
            'pegawai_id' => $pegawais[0],
            'created_at' => $beliTime2,
            'updated_at' => $beliTime2,
        ]);

        $produks2 = DB::table('produks')->whereNotIn('golongan', ['narkotika', 'psikotropika'])->inRandomOrder()->take(4)->get();

        foreach ($produks2 as $p) {
            $qty = rand(400, 800);
            $harga = $p->hargabeli ?? rand(10000, 50000);
            $subtotal = $qty * $harga;
            
            $batchId = DB::table('produkbatches')->insertGetId([
                'produks_id' => $p->id,
                'satuans_id' => $p->satuans_id ?? $sat_id,
                'unitprice' => $harga,
                'stok' => $qty,
                'tgl_produksi' => $beliTime2->copy()->subMonths(1)->toDateString(),
                'tgl_kadaluarsa' => $beliTime2->copy()->addYears(3)->toDateString(),
                'status' => 'tersedia',
                'distributors_id' => $dist_id,
                'gudangs_id' => $gud_id,
                'hpp_avg_per_unit' => $harga,
                'created_at' => $beliTime2,
                'updated_at' => $beliTime2,
            ]);
            
            DB::table('notabelis_has_produks')->insert([
                'notabelis_id' => $nb2_id,
                'produkbatches_id' => $batchId,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'created_at' => $beliTime2,
                'updated_at' => $beliTime2,
            ]);
        }

        $this->info("Menghitung ulang stok...");
        Artisan::call('app:sync-stock');

        $this->info("Berhasil! Grafik dan Total Pembelian bulan Juni kini telah terisi.");
    }
}
