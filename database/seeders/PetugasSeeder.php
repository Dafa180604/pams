<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        function generateIndonesianPhoneNumber(): string {
            return '08' . rand(111, 999) . rand(1000, 9999) . rand(100, 999);
        }

        DB::table('users')->insert([
            'id_users' => 1,
            'nama' => 'Dafa',
            'alamat' => 'watuduwur',
            'rw' => '1',
            'rt' => '2',
            'username' => 'dafaadmin',
            'no_hp' => '081233417452',
            'role' => 'admin',
            'password' => bcrypt('dafaadmin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 2,
            'nama' => 'Mahfudi',
            'alamat' => 'watuduwur',
            'rw' => '2',
            'rt' => '2',
            'username' => 'mahfudipetugas',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('mahfudipetugas'),
            'akses_pelanggan' => json_encode(["5", "6","7","8","9",]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 3,
            'nama' => 'Udhik',
            'alamat' => 'pulorjo',
            'rw' => '1',
            'rt' => '3',
            'username' => 'udhikpetugas',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('udhikpetugas'),
            'akses_pelanggan' => json_encode(["10", "11","12","13","14"]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 4,
            'nama' => 'Imam',
            'alamat' => 'Babadan',
            'rw' => '1',
            'rt' => '2',
            'username' => 'imampetugas',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('imampetugas'),
            'akses_pelanggan' => json_encode(["15", "16", "17","18","19"]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
