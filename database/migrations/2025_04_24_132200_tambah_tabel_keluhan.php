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
        Schema::create('keluhan', function (Blueprint $table) {
            $table->id('id_keluhan');
            $table->unsignedBigInteger('id_users');
            $table->string('keterangan', 500);
            $table->enum('status', ['Diproses', 'Dibaca', 'Terkirim']);
            $table->string('foto_keluhan')->nullable();
            $table->dateTime('tanggal');
            $table->string('tanggapan',500)->nullable();
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
        Schema::dropIfExists('keluhan');
    }
};
