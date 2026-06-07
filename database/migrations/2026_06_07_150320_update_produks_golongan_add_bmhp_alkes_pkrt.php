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
        DB::statement("ALTER TABLE produks MODIFY COLUMN golongan ENUM('bebas','terbatas','keras','narkotika','psikotropika','bmhp','alkes','pkrt') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUMs. Warning: any data using the new enums might cause an error when rolling back.
        DB::statement("ALTER TABLE produks MODIFY COLUMN golongan ENUM('bebas','terbatas','keras','narkotika','psikotropika') NOT NULL");
    }
};
