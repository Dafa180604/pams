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
            'username' => 'dafa001',
            'no_hp' => '081233417452',
            'role' => 'admin',
            'password' => bcrypt('Dafa@1'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 2,
            'nama' => 'Mahfudi',
            'alamat' => 'watuduwur',
            'rw' => '2',
            'rt' => '2',
            'username' => 'mahfudi002',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('Mahfudi@2'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 3,
            'nama' => 'Ngatemin',
            'alamat' => 'watuduwur',
            'rw' => '1',
            'rt' => '1',
            'username' => 'ngatemin003',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('Ngatemin@3'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 4,
            'nama' => 'Udhik',
            'alamat' => 'pulorjo',
            'rw' => '1',
            'rt' => '3',
            'username' => 'udhik004',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('Udhik@4'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id_users' => 5,
            'nama' => 'Imam',
            'alamat' => 'Babadan',
            'rw' => '1',
            'rt' => '2',
            'username' => 'imam005',
            'no_hp' => generateIndonesianPhoneNumber(),
            'role' => 'petugas',
            'password' => bcrypt('Imam@5'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
