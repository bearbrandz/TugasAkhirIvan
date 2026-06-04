<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produkopnames', function (Blueprint $table) {
            if (!Schema::hasColumn('produkopnames', 'produkbatches_id')) {
                $table->unsignedBigInteger('produkbatches_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('produkopnames', 'produks_id')) {
                $table->unsignedBigInteger('produks_id')->nullable()->after('produkbatches_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produkopnames', function (Blueprint $table) {
            if (Schema::hasColumn('produkopnames', 'produkbatches_id')) {
                $table->dropColumn('produkbatches_id');
            }

            if (Schema::hasColumn('produkopnames', 'produks_id')) {
                $table->dropColumn('produks_id');
            }
        });
    }
};