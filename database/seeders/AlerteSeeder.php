<?php

namespace Database\Seeders;

use App\Enums\GraviteAlerte;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Dossier;
use App\Models\Notification;
use App\Models\User;
use App\Services\AlerteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AlerteSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrFail();

        // Alertes de surestarie J-2 / J-1 / J0 calculées sur les conteneurs au port.
        app(AlerteService::class)->generer(false);

        // La maquette affiche 3 alertes non lues : la J-2 est déjà consultée.
        Alerte::where('titre', 'Franchise à J-2')->update(['lue' => true, 'lue_at' => now()->subDay()]);

        // Alertes métier complémentaires (documents, facturation, arrivée).
        $alerteDocument = $this->alerte($agence, [
            'dossier' => 'JL-2027-0034',
            'type' => 'document',
            'titre' => 'Document manquant',
            'description' => 'Certificat d\'origine · Dossier JL-2027-0034',
            'cible' => 'Certificat d\'origine',
            'gravite' => GraviteAlerte::Haute,
            'lue' => false,
            'jour' => 1,
        ]);

        $this->alerte($agence, [
            'dossier' => 'JL-2027-0031',
            'type' => 'document',
            'titre' => 'Liste de colisage rejetée',
            'description' => 'Scan illisible · Dossier JL-2027-0031',
            'cible' => 'Liste de colisage',
            'gravite' => GraviteAlerte::Moyenne,
            'lue' => true,
            'jour' => 2,
        ]);

        $this->alerte($agence, [
            'dossier' => 'JL-2027-0028',
            'type' => 'paiement',
            'titre' => 'Facture à échéance prochaine',
            'description' => 'FA-2027-0011 · Komia Distribution · Échéance '.Carbon::now()->addDays(15)->translatedFormat('j M'),
            'cible' => 'FA-2027-0011',
            'gravite' => GraviteAlerte::Moyenne,
            'lue' => true,
            'jour' => 2,
        ]);

        $this->alerte($agence, [
            'dossier' => 'JL-2027-0027',
            'type' => 'arrivee',
            'titre' => 'Date d\'arrivée proche',
            'description' => 'Dossier JL-2027-0027 · Navire MSKU 9012345',
            'cible' => 'Dossier JL-2027-0027',
            'gravite' => GraviteAlerte::Basse,
            'lue' => true,
            'jour' => 3,
        ]);

        $this->notifier($agence, $alerteDocument);
    }

    private function alerte(Agence $agence, array $donnees): Alerte
    {
        $dossier = Dossier::where('reference', $donnees['dossier'])->firstOrFail();

        return Alerte::create([
            'agence_id' => $agence->id,
            'dossier_id' => $dossier->id,
            'conteneur_id' => null,
            'type' => $donnees['type'],
            'titre' => $donnees['titre'],
            'description' => $donnees['description'],
            'cible' => $donnees['cible'],
            'echeance' => null,
            'gravite' => $donnees['gravite'],
            'lue' => $donnees['lue'],
            'lue_at' => $donnees['lue'] ? now()->subDays($donnees['jour']) : null,
            'date_alerte' => today()->subDays($donnees['jour']),
            'statut_envoi' => 'envoyee',
            'envoyee_at' => now()->subDays($donnees['jour']),
            'created_at' => now()->subDays($donnees['jour']),
            'updated_at' => now()->subDays($donnees['jour']),
        ]);
    }

    private function notifier(Agence $agence, Alerte $nouvelle): void
    {
        $utilisateurs = User::where('agence_id', $agence->id)
            ->whereIn('role', ['administrateur', 'transitaire', 'comptable'])
            ->get();

        foreach ($utilisateurs as $utilisateur) {
            Notification::create([
                'user_id' => $utilisateur->id,
                'alerte_id' => $nouvelle->id,
                'canal' => 'app',
                'titre' => $nouvelle->titre,
                'message' => $nouvelle->description,
                'lien' => '/alertes',
                'lue' => false,
            ]);
        }
    }
}
