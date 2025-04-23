<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class KategoriBiayaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori_biaya_air')->insert([
            'id_kategori_biaya' => 1,
            'batas_bawah' => 0,
            'batas_atas' => 15,
            'tarif' => 1500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('kategori_biaya_air')->insert([
            'id_kategori_biaya' => 2,
            'batas_bawah' => 16,
            'batas_atas' => 25,
            'tarif' => 2000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kategori_biaya_air')->insert([
            'id_kategori_biaya' => 3,
            'batas_bawah' => 26,
            'batas_atas' => 35,
            'tarif' => 2500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kategori_biaya_air')->insert([
            'id_kategori_biaya' => 4,
            'batas_bawah' => 36,
            'batas_atas' => 1000000,
            'tarif' => 3000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
