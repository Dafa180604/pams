<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganWatuduwurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pelanggan = [
            ['nama' => 'KASMANI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'KOSIM', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'SUPENO', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'KABUL', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'MASAMAH', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'BAKIR', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'NURKHABIB', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'SUPARLAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'HARI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'AHMAD', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'DUKI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'NURSALIM', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567805', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'LUKITO', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'AGUNG.P', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'WAHADI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'SULASTRI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'MULYATI', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567805', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'ERIK', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567806', 'jumlah_air' => rand(50, 200)],
            ['nama' => 'ADI', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567807', 'jumlah_air' => rand(50, 200)],
            //24
        ];

        $id = 6;

        foreach ($pelanggan as $data) {
            $namaSlug = strtolower(str_replace([' ', '.'], '', $data['nama']));
            DB::table('users')->insert([
                'id_users' => $id,
                'nama' => $data['nama'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'no_hp' => $data['no_hp'],
                'jumlah_air' => $data['jumlah_air'],
                'alamat' => 'Watuduwur',
                'golongan' => 'Bantuan',
                'role' => 'pelanggan',
                'username' => $namaSlug . str_pad($id, 3, '0', STR_PAD_LEFT),
                'password' => bcrypt($namaSlug . '@' . $id),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $id++;
        }
    }
}
