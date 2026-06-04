<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('racikans', function (Blueprint $table) {
            if (!Schema::hasColumn('racikans', 'bukti_resep')) {
                $table->string('bukti_resep')->nullable()->after('tanggal_pengambilan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('racikans', function (Blueprint $table) {
            if (Schema::hasColumn('racikans', 'bukti_resep')) {
                $table->dropColumn('bukti_resep');
            }
        });
    }
};