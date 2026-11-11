<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Dossier;
use App\Models\JournalActivite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrFail();
        $yao = User::where('email', 'comptable@jalon.tg')->firstOrFail();
        $afi = User::where('email', 'agent@jalon.tg')->firstOrFail();
        $kossi = User::where('email', 'admin@jalon.tg')->firstOrFail();

        $dossier = fn (string $ref) => Dossier::where('reference', $ref)->value('id');

        $entrees = [
            [
                'user_id' => $yao->id,
                'dossier_id' => $dossier('JL-2027-0031'),
                'action' => 'paiement',
                'description' => 'a enregistré un paiement de 200 000 FCFA',
                'sujet' => 'FA-2027-0012',
                'jours' => 1,
                'heure' => 10,
            ],
            [
                'user_id' => $afi->id,
                'dossier_id' => $dossier('JL-2027-0020'),
                'action' => 'bae',
                'description' => 'BAE délivré pour conteneur CMAU 2276405',
                'sujet' => 'JL-2027-0020',
                'jours' => 1,
                'heure' => 15,
            ],
            [
                'user_id' => $afi->id,
                'dossier_id' => $dossier('JL-2027-0034'),
                'action' => 'inspection',
                'description' => 'Inspection phytosanitaire validée au quai',
                'sujet' => 'JL-2027-0034',
                'jours' => 2,
                'heure' => 11,
            ],
            [
                'user_id' => $yao->id,
                'dossier_id' => $dossier('JL-2027-0028'),
                'action' => 'facture',
                'description' => 'Facture FA-2027-0011 émise pour Komia Distribution · Montant : 1 450 000 FCFA',
                'sujet' => 'FA-2027-0011',
                'jours' => 3,
                'heure' => 9,
            ],
            [
                'user_id' => $kossi->id,
                'dossier_id' => $dossier('JL-2027-0027'),
                'action' => 'manifeste',
                'description' => 'Nouveau manifeste import chargé depuis le Guichet Unique · Navire MSC Katrina',
                'sujet' => 'JL-2027-0027',
                'jours' => 4,
                'heure' => 16,
            ],
            [
                'user_id' => $afi->id,
                'dossier_id' => $dossier('JL-2027-0033'),
                'action' => 'dossier',
                'description' => 'Dossier JL-2027-0033 créé pour Mawuli Import (brouillon)',
                'sujet' => 'JL-2027-0033',
                'jours' => 6,
                'heure' => 8,
            ],
        ];

        foreach ($entrees as $entree) {
            $date = Carbon::now()->subDays($entree['jours'])->setTime($entree['heure'], 30);

            $entreeJournal = JournalActivite::create([
                'agence_id' => $agence->id,
                'user_id' => $entree['user_id'],
                'dossier_id' => $entree['dossier_id'],
                'action' => $entree['action'],
                'description' => $entree['description'],
                'sujet_type' => 'Dossier',
                'sujet_id' => $entree['dossier_id'],
            ]);
            $entreeJournal->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
        }
    }
}
