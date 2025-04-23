<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PelangganWatuduwurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [

            // Bustanil B
    ['id_users' => 1, 'nama' => 'KASMANI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 2, 'nama' => 'KOSIM', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 3, 'nama' => 'SUPENO', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 4, 'nama' => 'KABUL', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
    
    // Agung P
    ['id_users' => 38, 'nama' => 'MASAMAH', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 39, 'nama' => 'BAKIR', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 40, 'nama' => 'NURKHABIB', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 41, 'nama' => 'SUPARLAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
    
    // Mahfudi
    ['id_users' => 78, 'nama' => 'HARI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 79, 'nama' => 'AHMAD', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 80, 'nama' => 'DUKI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 81, 'nama' => 'NURSALIM', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567805', 'jumlah_air' => rand(50, 200)],
    
    // Ngatemin
    ['id_users' => 117, 'nama' => 'LUKITO', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 118, 'nama' => 'AGUNG.P', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 119, 'nama' => 'WAHADI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 120, 'nama' => 'SULASTRI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 121, 'nama' => 'MULYATI', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567805', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 122, 'nama' => 'ERIK', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567806', 'jumlah_air' => rand(50, 200)],
    ['id_users' => 123, 'nama' => 'ADI', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567807', 'jumlah_air' => rand(50, 200)],
];

        foreach ($data as $users) {
            DB::table('users')->insert(array_merge($users, [
                'alamat' => 'Watuduwur',
                'golongan' => 'Bantuan',
                'role' => 'pelanggan',
                'username' => strtolower(str_replace(' ', '', $users['nama'])) . '@gmail.com',
                'password' => bcrypt(strtolower(str_replace(' ', '', $users['nama'])) . 'gmail.com'),    
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
