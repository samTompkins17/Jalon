<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AgenceSeeder::class,
            RegimeSeeder::class,
            JalonSeeder::class,
            UtilisateurSeeder::class,
            ClientSeeder::class,
            DossierSeeder::class,
            FactureSeeder::class,
            AlerteSeeder::class,
            JournalSeeder::class,
        ]);
    }
}
