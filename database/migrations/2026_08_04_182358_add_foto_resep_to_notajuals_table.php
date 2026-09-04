<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up() 
    {
        Schema::table('notajuals', function (Blueprint $table) {
            $table->string('foto_resep')->nulllable()->after('dokter_id');
            
        });
    }

   
    public function down(): void
    {
        Schema::table('notajuals', function (Blueprint $table) {
            
        });
    }
};
