<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notajuals', function (Blueprint $table) {
            if (!Schema::hasColumn('notajuals', 'total_bayar')) {
                $table->double('total_bayar')->default(0)->after('pegawai_id');
            }

            if (!Schema::hasColumn('notajuals', 'nominal_bayar')) {
                $table->double('nominal_bayar')->default(0)->after('total_bayar');
            }

            if (!Schema::hasColumn('notajuals', 'kembalian')) {
                $table->double('kembalian')->default(0)->after('nominal_bayar');
            }

            if (!Schema::hasColumn('notajuals', 'metode_bayar')) {
                $table->string('metode_bayar')->default('tunai')->after('kembalian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notajuals', function (Blueprint $table) {
            if (Schema::hasColumn('notajuals', 'total_bayar')) {
                $table->dropColumn('total_bayar');
            }

            if (Schema::hasColumn('notajuals', 'nominal_bayar')) {
                $table->dropColumn('nominal_bayar');
            }

            if (Schema::hasColumn('notajuals', 'kembalian')) {
                $table->dropColumn('kembalian');
            }

            if (Schema::hasColumn('notajuals', 'metode_bayar')) {
                $table->dropColumn('metode_bayar');
            }
        });
    }
};