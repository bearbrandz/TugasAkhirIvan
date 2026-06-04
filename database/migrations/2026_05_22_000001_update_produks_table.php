<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds new columns to the `produks` table and
     * modifies existing ones to align with the specification in the PDF.
     *
     * Changes:
     *  - Adds `kode_produk` (string, nullable) after `nama`.
     *  - Adds `bentuk_sediaan` (string, nullable) after `golongan`.
     *  - Adds `stok_minimum` (integer, default 0) after `bentuk_sediaan`.
     *  - Expands the `golongan` enum to include `narkotika` and `psikotropika`.
     *  - Drops the `image` column which previously stored BLOB data for product images.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Add new optional SKU column
            $table->string('kode_produk')->nullable()->after('nama');
            // Add physical form / bentuk sediaan
            $table->string('bentuk_sediaan')->nullable()->after('golongan');
            // Add minimum stock threshold with a sensible default
            $table->integer('stok_minimum')->default(0)->after('bentuk_sediaan');
        });

        // Modify the enum list for `golongan` via raw SQL. This requires dropping
        // and re‑adding the enum definition as Laravel cannot alter enum
        // definitions directly without doctrine/dbal. It is safe here because
        // the existing values will be preserved if they match the new set.
        DB::statement("ALTER TABLE produks MODIFY COLUMN golongan ENUM('bebas','terbatas','keras','narkotika','psikotropika') NOT NULL");

        Schema::table('produks', function (Blueprint $table) {
            // Remove the unused image blob column, if present
            if (Schema::hasColumn('produks', 'image')) {
                $table->dropColumn('image');
            }
            // Remove fields no longer used by the system. We wrap these
            // operations in conditionals so the migration is safe to run
            // against an older schema where the columns might already be gone.
            if (Schema::hasColumn('produks', 'kategori')) {
                $table->dropColumn('kategori');
            }
            if (Schema::hasColumn('produks', 'satuan')) {
                $table->dropColumn('satuan');
            }
            if (Schema::hasColumn('produks', 'buyprice')) {
                $table->dropColumn('buyprice');
            }
            if (Schema::hasColumn('produks', 'margin')) {
                $table->dropColumn('margin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Drop newly added columns
            if (Schema::hasColumn('produks', 'kode_produk')) {
                $table->dropColumn('kode_produk');
            }
            if (Schema::hasColumn('produks', 'bentuk_sediaan')) {
                $table->dropColumn('bentuk_sediaan');
            }
            if (Schema::hasColumn('produks', 'stok_minimum')) {
                $table->dropColumn('stok_minimum');
            }
        });

        // Revert the enum back to the original three categories
        DB::statement("ALTER TABLE produks MODIFY COLUMN golongan ENUM('bebas','terbatas','keras') NOT NULL");

        Schema::table('produks', function (Blueprint $table) {
            // Restore previously dropped columns (without defaults)
            if (!Schema::hasColumn('produks', 'image')) {
                $table->binary('image')->nullable();
            }
            if (!Schema::hasColumn('produks', 'kategori')) {
                $table->string('kategori')->nullable();
            }
            if (!Schema::hasColumn('produks', 'satuan')) {
                $table->string('satuan')->nullable();
            }
            if (!Schema::hasColumn('produks', 'buyprice')) {
                $table->double('buyprice')->nullable();
            }
            if (!Schema::hasColumn('produks', 'margin')) {
                $table->double('margin')->nullable();
            }
        });
    }
};