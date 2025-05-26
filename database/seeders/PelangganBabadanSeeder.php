<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganBabadanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalData = [
            //Imam
            ['nama' => 'SUPARSIADI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 125],
            ['nama' => 'RUKIMAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 98],
            // ['nama' => 'SUJIMAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 140],
            // ['nama' => 'IMAM MAHSUN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 112],
            // ['nama' => 'PARMIATI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 132],
            
            //Dikin
            // ['nama' => 'SAHRI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 108],
            // ['nama' => 'ISNANDAR', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 121],
            // ['nama' => 'SUROTO', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 90],
            ['nama' => 'BUDI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 104],
            ['nama' => 'AINUROHMAH', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 115],
            
            //Samsul
            // ['nama' => 'BADRUD TAMAM', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 138],
            // ['nama' => 'ISTIKOMAH', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 99],
            // ['nama' => 'SAMSUDIN', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 142],
            // ['nama' => 'SANTO', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 110],
            ['nama' => 'NASROKHIM', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 129],
            
            //Partin
            // ['nama' => 'ZAINI', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 97],
            // ['nama' => 'SITI ASRIPAH', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 133],
            // ['nama' => 'SUMAJI', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 102],
            ['nama' => 'SUYANTO', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 119],
            
            // //Dikin 2
            // ['nama' => 'MALIK', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 135],
            // ['nama' => 'KAMSI BAKSO', 'rt' => '1', 'rw' => '3', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 101],
            // ['nama' => 'MUAJI', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 127],
            // ['nama' => 'SOBIRIN', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 145],
            ['nama' => 'AMIRUL', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 111],
        ];

        $startId = 17; // Mulai dari id_users 30
        foreach ($originalData as $index => $user) {
            $id_users = $startId + $index;
            $nama = strtolower(str_replace(' ', '', $user['nama']));
            $username = $nama . str_pad($id_users, 3, '0', STR_PAD_LEFT);
            $password = $nama . '@' . $id_users;

            DB::table('users')->insert(array_merge($user, [
                'id_users' => $id_users,
                'alamat' => 'Babadan',
                'golongan' => 'Bantuan',
                'username' => $username,
                'password' => bcrypt($password),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
