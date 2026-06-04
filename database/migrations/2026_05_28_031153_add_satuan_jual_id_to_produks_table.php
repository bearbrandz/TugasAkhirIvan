<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'satuan_jual_id')) {
                $table->unsignedBigInteger('satuan_jual_id')
                    ->nullable()
                    ->after('sellingprice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'satuan_jual_id')) {
                $table->dropColumn('satuan_jual_id');
            }
        });
    }
};