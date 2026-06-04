<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates two tables: `retur_pembelians` to store
     * information about purchase returns and `retur_pembelian_items` to
     * record the individual items being returned. A purchase return is
     * linked to a `notabeli` (purchase invoice) and may contain many
     * returned items. The `retur_pembelian_items` table links each
     * returned product to its quantity and unit price. Note that
     * additional fields such as references to batch IDs could be added
     * later if required by the business rules.
     */
    public function up(): void
    {
        Schema::create('retur_pembelians', function (Blueprint $table) {
            $table->id();
            // Reference to the original purchase invoice. Nullable in case
            // legacy returns exist without a matching purchase.
            $table->unsignedBigInteger('notabelis_id')->nullable();
            // Date of the return.
            $table->date('tanggal_retur');
            // Total amount of the return in local currency.
            $table->double('total')->default(0);
            // Optional description for the return (e.g. reason for return).
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Foreign key to notabelis table. When a purchase is deleted,
            // returns remain but lose the link.
            $table->foreign('notabelis_id')->references('id')->on('notabelis')->onDelete('set null');
        });

        Schema::create('retur_pembelian_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retur_pembelian_id');
            $table->unsignedBigInteger('produk_id');
            // Quantity of product being returned.
            $table->integer('jumlah');
            // Unit price at which the product was originally purchased.
            $table->double('harga_satuan')->default(0);
            // Subtotal for this line (jumlah * harga_satuan). Although this
            // could be calculated on the fly, storing it simplifies
            // reporting and auditing.
            $table->double('subtotal')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('retur_pembelian_id')->references('id')->on('retur_pembelians')->onDelete('cascade');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_items');
        Schema::dropIfExists('retur_pembelians');
    }
};