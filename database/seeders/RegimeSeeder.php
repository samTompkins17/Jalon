<?php

namespace Database\Seeders;

use App\Models\DocumentRequisRegime;
use App\Models\Regime;
use App\Models\RegimeFranchise;
use Illuminate\Database\Seeder;

class RegimeSeeder extends Seeder
{
    public function run(): void
    {
        $regimes = [
            [
                'code' => 'C100',
                'nom' => 'Consommation directe',
                'description' => 'Mise à la consommation immédiate des marchandises destinées au marché togolais.',
                'ordre' => 1,
            ],
            [
                'code' => 'T1',
                'nom' => 'Transit',
                'description' => 'Marchandises en transit vers un pays de l\'hinterland (Burkina, Niger, Mali).',
                'ordre' => 2,
            ],
            [
                'code' => 'RX',
                'nom' => 'Réexportation',
                'description' => 'Marchandises réexportées après passage portuaire.',
                'ordre' => 3,
            ],
            [
                'code' => 'EN',
                'nom' => 'Entrepôt',
                'description' => 'Stockage sous douane en entrepôt agréé.',
                'ordre' => 4,
            ],
        ];

        $documentsParDefaut = [
            'Facture commerciale',
            'Certificat d\'origine',
            'Liste de colisage',
            'Connaissement (B/L)',
            'Déclaration d\'importation',
            'Attestation ECTN / BESC',
        ];

        foreach ($regimes as $donnees) {
            $regime = Regime::firstOrCreate(['code' => $donnees['code']], $donnees);

            foreach ($documentsParDefaut as $index => $libelle) {
                DocumentRequisRegime::firstOrCreate(
                    ['regime_id' => $regime->id, 'libelle' => $libelle],
                    ['obligatoire' => true, 'ordre' => $index + 1]
                );
            }

            // Délai de franchise par ligne maritime (4 jours par défaut, comme la maquette).
            foreach (['MSC' => 4, 'Maersk' => 5, 'CMA CGM' => 4, 'Hapag-Lloyd' => 6, '*' => 4] as $ligne => $jours) {
                RegimeFranchise::firstOrCreate(
                    ['regime_id' => $regime->id, 'ligne_maritime' => $ligne],
                    ['jours' => $jours]
                );
            }
        }
    }
}
