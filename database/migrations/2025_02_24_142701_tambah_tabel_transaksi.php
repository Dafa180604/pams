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
        Schema::create('transaksi',function(Blueprint $table){
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_pemakaian');
            $table->unsignedBigInteger('id_beban_biaya');
            $table->unsignedBigInteger('id_kategori_biaya')->nullable();
            $table->text('detail_biaya');
            $table->unsignedBigInteger('id_biaya_denda')->nullable();
            $table->integer('rp_denda')->nullable();
            $table->integer('jumlah_rp');
            $table->enum('status_pembayaran', ['Lunas', 'Belum Bayar']);
            $table->dateTime('tgl_pembayaran')->nullable();
            $table->integer('uang_bayar')->nullable();
            $table->integer('kembalian')->nullable();
            $table->timestamps();
            $table->foreign('id_pemakaian')
              ->references('id_pemakaian')
              ->on('pemakaian')
              ->onDelete('cascade');
            $table->foreign('id_beban_biaya')
              ->references('id_beban_biaya')
              ->on('biaya_beban')
              ->onDelete('cascade');
            $table->foreign('id_kategori_biaya')
              ->references('id_kategori_biaya')
              ->on('kategori_biaya_air')
              ->onDelete('cascade');
            $table->foreign('id_biaya_denda')
              ->references('id_biaya_denda')
              ->on('biaya_denda')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
