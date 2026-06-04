<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a new `hpp_avg_per_unit` column to the produkbatches table
     * to store the current average cost per unit for each batch. This
     * value is derived from `hpp_records` and updated whenever
     * stock-related transactions occur. Using a dedicated column
     * simplifies queries for inventory valuation and profit/loss.
     */
    public function up(): void
    {
        Schema::table('produkbatches', function (Blueprint $table) {
            if (!Schema::hasColumn('produkbatches', 'hpp_avg_per_unit')) {
                $table->double('hpp_avg_per_unit')->default(0)->after('unitprice');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produkbatches', function (Blueprint $table) {
            if (Schema::hasColumn('produkbatches', 'hpp_avg_per_unit')) {
                $table->dropColumn('hpp_avg_per_unit');
            }
        });
    }
};