<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
       public function up()
    {
        Schema::table('produkbatches', function (Blueprint $table) {
            $table->unsignedBigInteger('satuan_beli_awal_id')->nullable()->after('satuans_id');
        });
    }

    public function down()
    {
        Schema::table('produkbatches', function (Blueprint $table) {
            $table->dropColumn('satuan_beli_awal_id');
        });
    }

};
