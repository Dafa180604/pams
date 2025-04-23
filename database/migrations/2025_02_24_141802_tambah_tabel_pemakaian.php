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
        Schema::create('pemakaian',function(Blueprint $table){
            $table->id('id_pemakaian');
            $table->unsignedBigInteger('id_users');
            $table->integer('meter_awal');
            $table->integer('meter_akhir');
            $table->integer('jumlah_pemakaian');
            $table->string('foto_meteran');
            $table->dateTime('waktu_catat');
            $table->string('petugas');
            $table->timestamps();

            $table->foreign('id_users')
              ->references('id_users')
              ->on('users')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian');
    }
};
