<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notabelis', function (Blueprint $table) {
            // Kita tambahkan distributors_id. Boleh nullable dulu untuk data lama.
            $table->integer('distributors_id')->nullable()->after('pegawai_id');
            $table->foreign('distributors_id')->references('id')->on('distributors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notabelis', function (Blueprint $table) {
            $table->dropForeign(['distributors_id']);
            $table->dropColumn('distributors_id');
        });
    }
};
