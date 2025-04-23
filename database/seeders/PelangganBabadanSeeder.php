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
        $data = [
            //Imam
            ['id_users' => 168, 'nama' => 'SUPARSIADI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 125],
            ['id_users' => 169, 'nama' => 'RUKIMAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 98],
            ['id_users' => 170, 'nama' => 'SUJIMAN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 140],
            ['id_users' => 171, 'nama' => 'IMAM MAHSUN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 112],
            ['id_users' => 172, 'nama' => 'PARMIATI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 132],
            ['id_users' => 173, 'nama' => 'IMAM FAHRUROJI', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567806', 'role' => 'pelanggan', 'jumlah_air' => 156],
            
            //Dikin
            ['id_users' => 250, 'nama' => 'SAHRI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 108],
            ['id_users' => 251, 'nama' => 'ISNANDAR', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 121],
            ['id_users' => 252, 'nama' => 'SUROTO', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 90],
            ['id_users' => 253, 'nama' => 'BUDI', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 104],
            ['id_users' => 254, 'nama' => 'AINUROHMAH', 'rt' => '1', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 115],
            
            //Samsul
            ['id_users' => 328, 'nama' => 'BADRUD TAMAM', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 138],
            ['id_users' => 329, 'nama' => 'ISTIKOMAH', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 99],
            ['id_users' => 330, 'nama' => 'SAMSUDIN', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 142],
            ['id_users' => 331, 'nama' => 'SANTO', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 110],
            ['id_users' => 332, 'nama' => 'NASROKHIM', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 129],
            
            //Partin
            ['id_users' => 411, 'nama' => 'ZAINI', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 97],
            ['id_users' => 412, 'nama' => 'SITI ASRIPAH', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 133],
            ['id_users' => 413, 'nama' => 'SUMAJI', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 102],
            ['id_users' => 414, 'nama' => 'SUYANTO', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 119],
            
            //Dikin 2
            ['id_users' => 497, 'nama' => 'MALIK', 'rt' => '2', 'rw' => '3', 'no_hp' => '081234567801', 'role' => 'pelanggan', 'jumlah_air' => 135],
            ['id_users' => 498, 'nama' => 'KAMSI BAKSO', 'rt' => '1', 'rw' => '3', 'no_hp' => '081234567802', 'role' => 'pelanggan', 'jumlah_air' => 101],
            ['id_users' => 499, 'nama' => 'MUAJI', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567803', 'role' => 'pelanggan', 'jumlah_air' => 127],
            ['id_users' => 500, 'nama' => 'SOBIRIN', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567804', 'role' => 'pelanggan', 'jumlah_air' => 145],
            ['id_users' => 501, 'nama' => 'AMIRUL', 'rt' => '3', 'rw' => '2', 'no_hp' => '081234567805', 'role' => 'pelanggan', 'jumlah_air' => 111],
];

        foreach ($data as $users) {
            DB::table('users')->insert(array_merge($users, [
                'alamat' => 'Babadan',
                'golongan' => 'Bantuan',
                'username' => strtolower(str_replace(' ', '', $users['nama'])) . '@gmail.com',
                'password' => bcrypt(strtolower(str_replace(' ', '', $users['nama'])) . 'gmail.com'),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

