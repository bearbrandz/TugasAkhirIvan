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
        Schema::table('notajuals', function (Blueprint $table) {
            $table->dropColumn(['nama_pasien', 'nama_dokter', 'alamat_pasien', 'alamat_dokter']);
            $table->foreignId('pasien_id')->nullable()->constrained('pasiens')->onDelete('set null');
            $table->foreignId('dokter_id')->nullable()->constrained('dokters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notajuals', function (Blueprint $table) {
            $table->dropForeign(['pasien_id']);
            $table->dropForeign(['dokter_id']);
            $table->dropColumn(['pasien_id', 'dokter_id']);
            
            $table->string('nama_pasien')->nullable();
            $table->string('nama_dokter')->nullable();
            $table->text('alamat_pasien')->nullable();
            $table->text('alamat_dokter')->nullable();
        });
    }
};
