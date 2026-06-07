<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all products where kode_produk is null or empty
        $produks = DB::table('produks')
            ->whereNull('kode_produk')
            ->orWhere('kode_produk', '=', '')
            ->get();

        foreach ($produks as $produk) {
            $prefix = 'OBT-';
            if ($produk->golongan === 'bmhp') $prefix = 'BHP-';
            elseif ($produk->golongan === 'alkes') $prefix = 'ALK-';
            elseif ($produk->golongan === 'pkrt') $prefix = 'PKR-';

            $kode = $prefix . str_pad($produk->id, 4, '0', STR_PAD_LEFT);
            DB::table('produks')
                ->where('id', $produk->id)
                ->update(['kode_produk' => $kode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed since we are just populating missing data
    }
};
