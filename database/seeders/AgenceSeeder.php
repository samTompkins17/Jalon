<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\ParametreAgence;
use Illuminate\Database\Seeder;

class AgenceSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrCreate(
            ['slug' => 'agence-transit-maritime'],
            [
                'nom' => 'Agence Transit Maritime',
                'email' => 'contact@transit-lome.tg',
                'telephone' => '+228 22 27 41 90',
                'adresse' => 'Boulevard du Mono, Zone portuaire',
                'ville' => 'Lomé',
                'pays' => 'Togo',
                'numero_agrement' => 'AG-2027-014',
                'type' => 'agence',
                'devise' => 'FCFA',
                // Numérotation de démonstration : prochains numéros JL-2027-0035,
                // FA-2027-0013 et CLI-2027-019.
                'dossier_annee' => 2027,
                'dossier_increment' => 35,
                'facture_annee' => 2027,
                'facture_increment' => 13,
                'client_annee' => 2027,
                'client_increment' => 19,
                'tva_taux' => 18,
                'delai_franchise_defaut' => 4,
            ]
        );

        foreach (ParametreAgence::valeursParDefaut() as $cle => $valeur) {
            ParametreAgence::firstOrCreate(
                ['agence_id' => $agence->id, 'cle' => $cle],
                ['valeur' => $valeur, 'groupe' => str_contains($cle, 'alerte') ? 'alertes' : 'general']
            );
        }
    }
}
