<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hpp_records', function (Blueprint $table) {
            if (!Schema::hasColumn('hpp_records', 'produkbatches_id')) {
                $table->unsignedBigInteger('produkbatches_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hpp_records', function (Blueprint $table) {
            if (Schema::hasColumn('hpp_records', 'produkbatches_id')) {
                $table->dropColumn('produkbatches_id');
            }
        });
    }
};