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
        Schema::create('kategori_biaya_air',function(Blueprint $table){
            $table->id('id_kategori_biaya');
            $table->integer('batas_bawah');
            $table->integer('batas_atas');
            $table->integer('tarif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_biaya_air');
    }
};
