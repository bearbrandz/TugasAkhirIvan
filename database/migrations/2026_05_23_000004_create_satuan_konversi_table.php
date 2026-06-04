<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The satuan_konversi table defines conversion factors between units
     * (satuans). Each record defines how many units of `dari_id` equal
     * one unit of `ke_id`. For example, if there are 10 tablets in one
     * strip, the record would have satuan_dari_id pointing to the
     * tablet unit, satuan_ke_id pointing to the strip unit and
     * nilai_konversi = 10. This table facilitates multi-satuan
     * operations such as purchasing in strips but selling by tablet.
     */
    public function up(): void
    {
        Schema::create('satuan_konversi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('satuan_dari_id');
            $table->unsignedBigInteger('satuan_ke_id');
            // How many of the source unit make up one target unit. We use
            // a decimal column to support fractional conversions.
            $table->decimal('nilai_konversi', 12, 4);
            $table->timestamps();

            // Foreign keys referencing the satuans table
            $table->foreign('satuan_dari_id')->references('id')->on('satuans')->onDelete('cascade');
            $table->foreign('satuan_ke_id')->references('id')->on('satuans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_konversi');
    }
};