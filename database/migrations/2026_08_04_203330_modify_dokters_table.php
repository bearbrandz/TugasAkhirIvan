<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokters', function (Blueprint $table) {
            $table->dropColumn('spesialis'); // Menghapus kolom spesialis
            $table->string('sip')->nullable()->change(); // Membuat SIP opsional
        });
    }

    public function down(): void
    {
        Schema::table('dokters', function (Blueprint $table) {
            $table->string('spesialis')->nullable();
            $table->string('sip')->nullable(false)->change();
        });
    }
};
