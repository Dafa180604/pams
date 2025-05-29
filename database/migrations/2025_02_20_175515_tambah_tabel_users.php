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
        Schema::create('users',function(Blueprint $table){
            $table->id('id_users');
            $table->string('nama');
            $table->string('alamat');
            $table->string('rw');
            $table->string('rt');
            $table->string('no_hp')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('foto_profile')->nullable();
            $table->enum('golongan', ['Bantuan', 'Berbayar'])->nullable();
            $table->integer('jumlah_air')->nullable();
            $table->string('akses_pelanggan',500)->nullable();
            $table->timestamps();
            $table->softDeletes(); // Menambahkan kolom deleted_at untuk soft delete
        });
        
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions'); // Juga tambahkan dropIfExists untuk tabel sessions
    }
};