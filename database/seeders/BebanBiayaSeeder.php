<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BebanBiayaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('biaya_beban')->insert([
            'id_beban_biaya' => 1,
            'tarif' => 5000,
            'keterangan' => "Tarif tersebut adalah nilai untuk ditambahkan setiap transaksi yang berjalan.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
