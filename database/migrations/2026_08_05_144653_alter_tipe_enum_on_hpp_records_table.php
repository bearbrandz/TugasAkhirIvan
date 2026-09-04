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
        DB::statement("ALTER TABLE hpp_records MODIFY COLUMN tipe ENUM('pembelian','retur','penyesuaian') NOT NULL DEFAULT 'pembelian'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE hpp_records MODIFY COLUMN tipe ENUM('pembelian','retur') NOT NULL DEFAULT 'pembelian'");
    }
};
