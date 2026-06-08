<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SidangDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("Memulai injeksi data historis untuk Sidang (27 Mei - 7 Juni 2026)...");

        // 1. Setup Data Pegawai
        $pegawais = DB::table('users')->whereIn('tipe_user', ['kasir', 'apoteker', 'admin'])->pluck('id')->toArray();
        if (empty($pegawais)) {
            $this->command->error("Tidak ada user untuk dijadikan pegawai!");
            return;
        }

        // 2. Buat Racikan Non-Narkotika Baru (Batuk Berdahak / Flu Radang)
        $existingRacikan = DB::table('racikans')->where('nama', 'Obat Racik Flu & Radang (Dewasa)')->first();
        if ($existingRacikan) {
            $racikanBiasaId = $existingRacikan->id;
        } else {
            $racikanBiasaId = DB::table('racikans')->insertGetId([
                'nama' => 'Obat Racik Flu & Radang (Dewasa)',
                'biaya_embalase' => 10000,
                'deskripsi' => 'Racikan untuk meringankan gejala flu berat dan peradangan tenggorokan.',
                'nama_dokter' => 'dr. Andi Hidayat',
                'nama_pasien' => 'Suharjo',
                'aturan_pakai' => '3x Sehari sesudah makan',
                'alamat_dokter' => 'Jl. Pahlawan No. 12, Madiun',
                'alamat_pasien' => 'Jl. Diponegoro No. 4, Madiun',
                'tgl_ambil' => '2026-05-26',
                'created_at' => Carbon::create(2026, 5, 26, 8, 0, 0),
                'updated_at' => Carbon::create(2026, 5, 26, 8, 0, 0),
            ]);

            // Komposisi: Paracetamol 500mg (Produk#38) x 10, Amoxicillin 500mg (Produk#49) x 10
            DB::table('racikanproduks')->insert([
                ['racikans_id' => $racikanBiasaId, 'produks_id' => 38, 'quantity' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['racikans_id' => $racikanBiasaId, 'produks_id' => 49, 'quantity' => 10, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->command->info("Dibuat Racikan Baru: Obat Racik Flu & Radang (Dewasa)");
        }

        // Kumpulkan data racikans untuk dipilih secara acak
        $racikanIds = DB::table('racikans')->pluck('id')->toArray();

        // 3. Looping Hari
        $startDate = Carbon::create(2026, 5, 27);
        $endDate = Carbon::create(2026, 6, 7);
        $currentDate = $startDate->copy();

        $notaBeliIds = DB::table('notabelis')->pluck('id')->toArray();

        while ($currentDate->lte($endDate)) {
            $this->command->info("Generating data untuk tanggal: " . $currentDate->format('Y-m-d'));

            // Jam buka apotek: 08:00 - 21:00
            $jmlTransaksi = rand(5, 12); // lumayan ramai

            for ($i = 0; $i < $jmlTransaksi; $i++) {
                $transTime = $currentDate->copy()->setHour(rand(8, 20))->setMinute(rand(0, 59))->setSecond(rand(0, 59));

                $tipeTransaksi = rand(1, 10);
                $isRacikan = ($tipeTransaksi <= 3); // 30% transaksi mengandung racikan

                $totalTransaksi = 0;
                
                // Buat Header Notajual
                $notajualId = DB::table('notajuals')->insertGetId([
                    'nomor_nota' => 'NJ-' . $transTime->format('Ymd') . '-' . Str::random(4),
                    'pegawai_id' => $pegawais[array_rand($pegawais)],
                    'total_bayar' => 0, // Akan diupdate nanti
                    'nominal_bayar' => 0,
                    'kembalian' => 0,
                    'metode_bayar' => rand(1, 10) > 7 ? 'debit' : 'tunai',
                    'created_at' => $transTime,
                    'updated_at' => $transTime,
                ]);

                // Item transaksi
                if ($isRacikan) {
                    $selectedRacikanId = $racikanIds[array_rand($racikanIds)];
                    $racikan = DB::table('racikans')->where('id', $selectedRacikanId)->first();
                    $komposisi = DB::table('racikanproduks')->where('racikans_id', $selectedRacikanId)->get();
                    
                    $jumlahRacikanDipesan = 1;
                    $subtotalRacikan = $racikan->biaya_embalase;

                    // Kurangi stok untuk tiap komposisi racikan
                    foreach ($komposisi as $komp) {
                        $qtyDibutuhkan = $komp->quantity * $jumlahRacikanDipesan;
                        
                        // Cari batch
                        $batches = DB::table('produkbatches')
                            ->where('produks_id', $komp->produks_id)
                            ->where('stok', '>', 0)
                            ->orderBy('id', 'asc')
                            ->get();

                        foreach ($batches as $batch) {
                            if ($qtyDibutuhkan <= 0) break;
                            $terjual = min($qtyDibutuhkan, $batch->stok);

                            // Ambil harga (Markup)
                            $produk = DB::table('produks')->where('id', $komp->produks_id)->first();
                            $hpp = $batch->hpp_avg_per_unit;
                            if (!$hpp || $hpp <= 0) $hpp = $produk->hargabeli ?? 1000;
                            $finalPrice = round($hpp * (1 + ($produk->sellingprice / 100)), 0);

                            $subtotalProduk = $terjual * $finalPrice;
                            $subtotalRacikan += $subtotalProduk;

                            DB::table('produkbatches')->where('id', $batch->id)->decrement('stok', $terjual);

                            DB::table('notajuals_has_produks')->insert([
                                'notajuals_id' => $notajualId,
                                'produkbatches_id' => $batch->id,
                                'quantity' => $terjual,
                                'subtotal' => $subtotalProduk,
                                'created_at' => $transTime,
                                'updated_at' => $transTime,
                            ]);

                            $qtyDibutuhkan -= $terjual;
                        }
                    }

                    DB::table('notajuals_has_racikans')->insert([
                        'notajuals_id' => $notajualId,
                        'racikans_id' => $selectedRacikanId,
                        'quantity' => $jumlahRacikanDipesan,
                        'subtotal' => $subtotalRacikan,
                        'created_at' => $transTime,
                        'updated_at' => $transTime,
                    ]);

                    $totalTransaksi += $subtotalRacikan;
                } else {
                    // Penjualan Reguler
                    $jmlItem = rand(1, 4);
                    // Ambil beberapa produk sekaligus agar unik
                    $produkAcaks = DB::table('produks')
                        ->whereNotIn('golongan', ['narkotika', 'psikotropika'])
                        ->inRandomOrder()
                        ->take($jmlItem)
                        ->get();

                    foreach ($produkAcaks as $produkAcak) {
                        $qtyDibutuhkan = rand(1, 5);

                        $batches = DB::table('produkbatches')
                            ->where('produks_id', $produkAcak->id)
                            ->where('stok', '>', 0)
                            ->orderBy('id', 'asc')
                            ->get();

                        foreach ($batches as $batch) {
                            if ($qtyDibutuhkan <= 0) break;
                            $terjual = min($qtyDibutuhkan, $batch->stok);

                            $hpp = $batch->hpp_avg_per_unit;
                            if (!$hpp || $hpp <= 0) $hpp = $produkAcak->hargabeli ?? 1000;
                            $finalPrice = round($hpp * (1 + ($produkAcak->sellingprice / 100)), 0);

                            $subtotalProduk = $terjual * $finalPrice;
                            $totalTransaksi += $subtotalProduk;

                            DB::table('produkbatches')->where('id', $batch->id)->decrement('stok', $terjual);

                            DB::table('notajuals_has_produks')->insert([
                                'notajuals_id' => $notajualId,
                                'produkbatches_id' => $batch->id,
                                'quantity' => $terjual,
                                'subtotal' => $subtotalProduk,
                                'created_at' => $transTime,
                                'updated_at' => $transTime,
                            ]);

                            $qtyDibutuhkan -= $terjual;
                        }
                    }
                }

                // Update Notajual Header
                $nominalBayar = ceil($totalTransaksi / 50000) * 50000;
                if ($nominalBayar < $totalTransaksi) $nominalBayar = $totalTransaksi;

                DB::table('notajuals')->where('id', $notajualId)->update([
                    'total_bayar' => $totalTransaksi,
                    'nominal_bayar' => $nominalBayar,
                    'kembalian' => $nominalBayar - $totalTransaksi,
                ]);
            }

            // 4. Retur Pembelian (Seminggu Sekali)
            if (in_array($currentDate->format('Y-m-d'), ['2026-05-30', '2026-06-05'])) {
                $this->command->info("Membuat Retur Pembelian...");
                
                $returTime = $currentDate->copy()->setHour(14);
                
                // Cari notabeli acak yang punya stok banyak
                $nb = DB::table('notabelis as nb')
                    ->join('notabelis_has_produks as nhp', 'nb.id', '=', 'nhp.notabelis_id')
                    ->join('produkbatches as pb', 'nhp.produkbatches_id', '=', 'pb.id')
                    ->where('pb.stok', '>', 5)
                    ->select('nb.id as nota_id', 'pb.id as batch_id', 'pb.produks_id', 'pb.unitprice')
                    ->inRandomOrder()
                    ->first();

                if ($nb) {
                    $returId = DB::table('retur_pembelians')->insertGetId([
                        'no_retur' => 'RET-' . $returTime->format('ymd') . '-' . rand(1000, 9999),
                        'notabelis_id' => $nb->nota_id,
                        'pegawai_id' => $pegawais[array_rand($pegawais)],
                        'tanggal_retur' => $returTime->toDateString(),
                        'tgl_retur' => $returTime->toDateString(),
                        'total' => $nb->unitprice * 2,
                        'total_retur' => $nb->unitprice * 2,
                        'alasan' => 'rusak',
                        'keterangan' => 'Kemasan obat penyok dari distributor',
                        'created_at' => $returTime,
                        'updated_at' => $returTime,
                    ]);

                    DB::table('retur_pembelian_details')->insert([
                        'retur_pembelian_id' => $returId,
                        'produkbatches_id' => $nb->batch_id,
                        'produks_id' => $nb->produks_id,
                        'qty' => 2,
                        'harga_satuan' => $nb->unitprice,
                        'subtotal' => $nb->unitprice * 2,
                        'alasan' => 'rusak',
                        'created_at' => $returTime,
                        'updated_at' => $returTime,
                    ]);

                    DB::table('produkbatches')->where('id', $nb->batch_id)->decrement('stok', 2);
                }
            }

            // 5. Stok Opname (Setiap 6 hari sekali)
            if (in_array($currentDate->format('Y-m-d'), ['2026-06-01', '2026-06-06'])) {
                $this->command->info("Membuat Stok Opname...");
                $opTime = $currentDate->copy()->setHour(20);

                // Ambil 1 batch acak
                $batch = DB::table('produkbatches')->where('stok', '>', 5)->inRandomOrder()->first();
                if ($batch) {
                    $stokFisik = $batch->stok - 1; // Hilang 1
                    
                    DB::table('produkopnames')->insert([
                        'produkbatches_id' => $batch->id,
                        'tanggal' => $opTime->toDateString(),
                        'produks_id' => $batch->produks_id,
                        'stok_sistem' => $batch->stok,
                        'stok_fisik' => $stokFisik,
                        'selisih' => -1,
                        'keterangan' => 'Selisih 1 tablet hilang (tercecer)',
                        'users_id' => $pegawais[array_rand($pegawais)],
                        'created_at' => $opTime,
                        'updated_at' => $opTime,
                    ]);

                    DB::table('produkbatches')->where('id', $batch->id)->decrement('stok', 1);
                }
            }

            $currentDate->addDay();
        }

        $this->command->info("Injeksi data selesai!");
        
        $this->command->info("Menjalankan SyncStockCommand untuk merapikan kalkulasi stok akhir...");
        Artisan::call('app:sync-stock');
        
        $this->command->info("Proses selesai sempurna!");
    }
}
