<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            PelangganBabadanSeeder::class,
            PelangganPulorjoSeeder::class,
            PelangganWatuduwurSeeder::class,
            PetugasSeeder::class,
            KategoriBiayaSeeder::class,
            BiayaDendaSeeder::class,
            BebanBiayaSeeder::class,
            BiayaGolonganSeeder::class,
            SaldoLaluSeeder::class,
        ]);
    }
}
