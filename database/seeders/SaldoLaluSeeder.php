<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class SaldoLaluSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('laporan')->insert([
            'id_laporan'  => 1,
            'keterangan'  => 'Saldo Bulan Lalu',
            'tanggal'     => Carbon::now(),
            'uang_masuk'  => 10000000,
            'uang_keluar' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
