<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The hpp_records table stores a history of average cost (HPP – Harga
     * Perolehan Persediaan) changes for each batch of a product. Whenever
     * inventory enters or leaves the system (purchase, sale, return or
     * stock adjustment) the system should record the previous average
     * cost, the quantity involved, and the resulting new average cost.
     */
    public function up(): void
    {
        Schema::create('hpp_records', function (Blueprint $table) {
            $table->id();
            // Reference to the batch whose HPP changes. We make this nullable
            // because historic records imported from legacy data may not
            // explicitly link to a batch.
            $table->unsignedBigInteger('produkbatches_id')->nullable();
            $table->unsignedBigInteger('produks_id')->nullable();
            // The date/time when the HPP change occurred.
            $table->timestamp('tanggal')->useCurrent();
            // Quantity of items that triggered the HPP change. Positive values
            // indicate stock coming in (e.g. purchases) while negative
            // values indicate stock leaving (e.g. sales or returns out).
            $table->integer('qty_change');
            // Price per unit of the transaction that triggered the HPP change.
            $table->double('harga_satuan')->default(0);
            // Previous average cost before applying this transaction.
            $table->double('hpp_sebelum')->default(0);
            // New average cost after the transaction is applied.
            $table->double('hpp_sesudah')->default(0);
            // Optional description or reference (e.g. invoice number).
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Indexes and foreign keys
            $table->foreign('produkbatches_id')->references('id')->on('produkbatches')->onDelete('set null');
            $table->foreign('produks_id')->references('id')->on('produks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpp_records');
    }
};