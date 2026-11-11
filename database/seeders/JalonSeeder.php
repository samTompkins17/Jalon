<?php

namespace Database\Seeders;

use App\Models\JalonConfig;
use Illuminate\Database\Seeder;

class JalonSeeder extends Seeder
{
    public function run(): void
    {
        foreach (JalonConfig::valeursParDefaut() as $ordre => $jalon) {
            JalonConfig::firstOrCreate(
                ['ordre' => $ordre],
                [
                    'nom' => $jalon['nom'],
                    'condition_passage' => $jalon['condition'],
                    'visible_client' => true,
                ]
            );
        }

        JalonConfig::oublierCache();
    }
}
