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
            $table->unsignedBigInteger('id_users')->nullable();
            $table->string('keterangan');
            $table->string('status');
            $table->string('foto_keluhan')->nullable();
            $table->dateTime('tanggal');
            $table->string('tanggapan')->nullable();
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
