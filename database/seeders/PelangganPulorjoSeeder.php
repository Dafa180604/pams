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
            //UDHIK
            ['id_users' => 155, 'nama' => 'SUMARDI', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567801','jumlah_air' => 120],
            ['id_users' => 156, 'nama' => 'WIN WIN', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567802','jumlah_air' => 98],
            ['id_users' => 157, 'nama' => 'BU FIROH', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567803','jumlah_air' => 135],
            ['id_users' => 158, 'nama' => 'MASJID PULOREJO (Utara)', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567804','jumlah_air' => 110],
            ['id_users' => 159, 'nama' => 'TISNA', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567805','jumlah_air' => 125],
            ['id_users' => 160, 'nama' => 'SUTRISNO', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567806','jumlah_air' => 140],
            ['id_users' => 161, 'nama' => 'MASJID PULOREJO (AL AMIN)', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567807','jumlah_air' => 95],
            ['id_users' => 162, 'nama' => 'MUDIONO', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567808','jumlah_air' => 130],
            ['id_users' => 163, 'nama' => 'TARMUJI', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567809','jumlah_air' => 115],
            ['id_users' => 164, 'nama' => 'MBAH JALIL PLRJ', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567810','jumlah_air' => 105],
            ['id_users' => 165, 'nama' => 'LULUK', 'rt' => '1', 'rw' => '1', 'no_hp' => '081234567811','jumlah_air' => 99],
            ['id_users' => 166, 'nama' => 'IMAM R.', 'rt' => '2', 'rw' => '1', 'no_hp' => '081234567812','jumlah_air' => 132],
            ['id_users' => 167, 'nama' => 'MAKAM PULOREJO', 'rt' => '2', 'rw' => '2', 'no_hp' => '081234567813','jumlah_air' => 118],
 ];

        foreach ($data as $users) {
            DB::table('users')->insert(array_merge($users, [
                'alamat' => 'Pulorjo',
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
