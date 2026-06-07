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
        $produks = DB::table('produks')->orderBy('id', 'asc')->get();
        
        $counters = [
            'OBT-' => 1,
            'BHP-' => 1,
            'ALK-' => 1,
            'PKR-' => 1,
        ];

        foreach ($produks as $produk) {
            $prefix = 'OBT-';
            if ($produk->golongan === 'bmhp') $prefix = 'BHP-';
            elseif ($produk->golongan === 'alkes') $prefix = 'ALK-';
            elseif ($produk->golongan === 'pkrt') $prefix = 'PKR-';

            $newNumber = $counters[$prefix]++;
            $kode = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

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
        // No down migration needed
    }
};
