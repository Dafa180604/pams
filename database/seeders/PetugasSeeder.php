<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
class petugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        DB::table('users')->insert([
            'id_users' => 11,
            'nama' => 'Dafa',
            'alamat' => 'watuduwur',
            'rw' => '1',
            'rt' => '2',
            'username' => 'dafa@gmail.com',     
            'no_hp' => substr($faker->unique()->phoneNumber, 0, 13),
            'role' => 'petugas',
            'password' => bcrypt('dafa@gmail.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id_users' => 14,
            'nama' => 'Mahfudi',
            'alamat' => 'watuduwur',
            'rw' => '2',
            'rt' => '2',
            'username' => 'mahfudi@gmail.com',   
            'no_hp' => substr($faker->unique()->phoneNumber, 0, 13),
            'role' => 'petugas',
            'password' => bcrypt('mahfudi@gmail.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id_users' => 15,
            'nama' => 'Ngatemin',
            'alamat' => 'watuduwur',
            'rw' => '1',
            'rt' => '1',
            'username' => 'ngatemin@gmail.com', 
            'no_hp' => substr($faker->unique()->phoneNumber, 0, 13),
            'role' => 'petugas',
            'password' => bcrypt('ngatemin@gmail.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id_users' => 16,
            'nama' => 'Udhik',
            'alamat' => 'pulorjo',
            'rw' => '1',
            'rt' => '3',
            'username' => 'udhik@gmail.com',
            'no_hp' => substr($faker->unique()->phoneNumber, 0, 13),
            'role' => 'petugas',
            'password' => bcrypt('udhik@gmail.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id_users' => 17,
            'nama' => 'Imam',
            'alamat' => 'Babadan',
            'rw' => '1',
            'rt' => '2',
            'username' => 'imam@gmail.com',
            'no_hp' => substr($faker->unique()->phoneNumber, 0, 13),
            'role' => 'petugas',
            'password' => bcrypt('imam@gmail.com'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
