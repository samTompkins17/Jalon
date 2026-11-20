<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Données de référence : nécessaires partout.
        $this->call([
            RegimeSeeder::class,
            JalonSeeder::class,
        ]);

        // Démonstration : jamais en production (comptes « password », dossiers fictifs).
        if (app()->environment('production')) {
            return;
        }

        $this->call([
            AgenceSeeder::class,
            UtilisateurSeeder::class,
            ClientSeeder::class,
            DossierSeeder::class,
            FactureSeeder::class,
            AlerteSeeder::class,
            JournalSeeder::class,
        ]);
    }
}
