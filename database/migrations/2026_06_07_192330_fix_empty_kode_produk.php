<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Produk;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $produks = Produk::whereNull('kode_produk')
            ->orWhere('kode_produk', '')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($produks as $produk) {
            $kode = Produk::generateKodeProduk($produk->golongan);
            $produk->kode_produk = $kode;
            $produk->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
