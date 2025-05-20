<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BiayaDendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('biaya_denda')->insert([
            'id_biaya_denda' => 1,
            'jumlah_telat' => 1,
            'biaya_telat' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('biaya_denda')->insert([
            'id_biaya_denda' => 2,
            'jumlah_telat' => 2,
            'biaya_telat' => 20000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
