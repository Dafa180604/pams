<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganPulorjoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // id_users mulai dari 6
            ['id_users' => 25, 'nama' => 'SUMARDI', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567801', 'jumlah_air' => 120],
            ['id_users' => 26, 'nama' => 'WIN WIN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'jumlah_air' => 98],
            ['id_users' => 27, 'nama' => 'BU FIROH', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567803', 'jumlah_air' => 135],
            ['id_users' => 28, 'nama' => 'MASJID PULOREJO (Utara)', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567804', 'jumlah_air' => 110],
            ['id_users' => 29, 'nama' => 'TISNA', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567805', 'jumlah_air' => 125],
        ];

        foreach ($data as $users) {
            $id = $users['id_users'];
            $nama = strtolower(str_replace(' ', '', $users['nama']));
            // Format username = nama + 3 digit id_users (ex: sumardi006)
            $username = $nama . str_pad($id, 3, '0', STR_PAD_LEFT);
            // Format password = nama@id_users (ex: sumardi@6)
            $password = $nama . '@' . $id;

            DB::table('users')->insert(array_merge($users, [
                'alamat' => 'Pulorjo',
                'golongan' => 'Bantuan',
                'role' => 'pelanggan',
                'username' => $username,
                'password' => bcrypt($password),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
