<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur_pembelians', function (Blueprint $table) {
            if (!Schema::hasColumn('retur_pembelians', 'tanggal_retur')) {
                $table->date('tanggal_retur')->nullable()->after('notabelis_id');
            }

            if (!Schema::hasColumn('retur_pembelians', 'total')) {
                $table->double('total')->default(0)->after('tanggal_retur');
            }

            if (!Schema::hasColumn('retur_pembelians', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('retur_pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('retur_pembelians', 'tanggal_retur')) {
                $table->dropColumn('tanggal_retur');
            }
        });
    }
};