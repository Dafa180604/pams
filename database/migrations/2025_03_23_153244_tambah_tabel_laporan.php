<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->string('keterangan');
            $table->dateTime('tanggal')->nullable();
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->unsignedBigInteger('id_biaya_golongan')->nullable();
            $table->integer('uang_masuk')->nullable();
            $table->integer('uang_keluar')->nullable();
            $table->timestamps();
            $table->foreign('id_transaksi')
                ->references('id_transaksi')
                ->on('transaksi')
                ->onDelete('cascade');
            $table->foreign('id_biaya_golongan')
                ->references('id_biaya_golongan')
                ->on('biaya_golongan_berbayar')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
