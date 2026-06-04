<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('retur_pembelian_details')) {
            Schema::create('retur_pembelian_details', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('retur_pembelian_id')->nullable();
                $table->unsignedBigInteger('notabelis_has_produks_id')->nullable();
                $table->unsignedBigInteger('produkbatches_id')->nullable();
                $table->unsignedBigInteger('produks_id')->nullable();

                $table->integer('qty')->default(0);
                $table->double('harga_satuan')->default(0);
                $table->double('subtotal')->default(0);
                $table->string('alasan')->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('retur_pembelian_details', function (Blueprint $table) {
                if (!Schema::hasColumn('retur_pembelian_details', 'retur_pembelian_id')) {
                    $table->unsignedBigInteger('retur_pembelian_id')->nullable();
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'notabelis_has_produks_id')) {
                    $table->unsignedBigInteger('notabelis_has_produks_id')->nullable();
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'produkbatches_id')) {
                    $table->unsignedBigInteger('produkbatches_id')->nullable();
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'produks_id')) {
                    $table->unsignedBigInteger('produks_id')->nullable();
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'qty')) {
                    $table->integer('qty')->default(0);
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'harga_satuan')) {
                    $table->double('harga_satuan')->default(0);
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'subtotal')) {
                    $table->double('subtotal')->default(0);
                }

                if (!Schema::hasColumn('retur_pembelian_details', 'alasan')) {
                    $table->string('alasan')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_details');
    }
};