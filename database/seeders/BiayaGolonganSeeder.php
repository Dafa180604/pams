<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BiayaGolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('biaya_golongan_berbayar')->insert([
            'id_biaya_golongan' => 1,
            'tarif' => 500000,
            'keterangan' => "Biaya Golongan dibuat untuk biaya awal pemasangan pelanggan yang tidak dapat bantuan",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
