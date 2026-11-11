<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Models\Agence;
use App\Models\Client;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrFail();
        $afi = User::where('email', 'agent@jalon.tg')->firstOrFail();
        $kossi = User::where('email', 'admin@jalon.tg')->firstOrFail();

        // [nom, type, contact, téléphone, e-mail, ville, transitaire]
        $clients = [
            ['Société Adjo Import', 'entreprise', 'Adjo K.', '+228 90 12 34 56', 'contact@adjoimport.tg', 'Lomé', $afi],
            ['Komia Distribution', 'entreprise', 'Komia A.', '+228 90 22 33 44', 'achats@komia.tg', 'Lomé', $afi],
            ['Ets Ayité et Fils', 'entreprise', 'Ayité F.', '+228 91 33 44 55', 'ayite@ayitefils.tg', 'Lomé', $afi],
            ['Afi Cosmétiques', 'entreprise', 'Afi M.', '+228 92 44 55 66', 'contact@aficosmetiques.tg', 'Lomé', $afi],
            ['Kodjo Négoce', 'particulier', 'Kodjo N.', '+228 93 55 66 77', 'kodjo.negoce@gmail.com', 'Kpalimé', $afi],
            ['Mawuli Import', 'entreprise', 'Mawuli T.', '+228 94 66 77 88', 'mawuli@mawuliimport.tg', 'Lomé', $kossi],
            ['Sika Matériaux', 'entreprise', 'Sika K.', '+228 95 77 88 99', 'achats@sikamateriaux.tg', 'Lomé', $afi],
            ['Ets Dossou & Frères', 'entreprise', 'Dossou P.', '+228 96 88 99 00', 'dossou@dossoufreres.tg', 'Tsévié', $afi],
            ['Togo Textile SA', 'entreprise', 'Amégan K.', '+228 97 99 00 11', 'contact@togotextile.tg', 'Lomé', $kossi],
            ['Hôtel du Golfe', 'entreprise', 'Mensah D.', '+228 98 00 11 22', 'achats@hoteldugolfe.tg', 'Lomé', $afi],
            ['Sarl Bénin Traders', 'entreprise', 'Houngbo S.', '+229 97 12 34 56', 'contact@benintraders.bj', 'Cotonou', $afi],
            ['Kpalimé Cacao Coop', 'entreprise', 'Agbeko Y.', '+228 90 13 24 35', 'coop@kpalimecacao.tg', 'Kpalimé', $kossi],
            ['Nana Assurances', 'entreprise', 'Nana B.', '+228 91 24 35 46', 'contact@nana-assur.tg', 'Lomé', $afi],
            ['Sotrabo Agro', 'entreprise', 'Sotrabo A.', '+228 92 35 46 57', 'achats@sotrabo.tg', 'Sokodé', $afi],
            ['Lomé Beach Resort', 'entreprise', 'Tété K.', '+228 93 46 57 68', 'direction@lomebeach.tg', 'Lomé', $afi],
            ['Ets Foli K.', 'particulier', 'Foli K.', '+228 94 57 68 79', 'foli.k@gmail.com', 'Aného', $kossi],
            ['Tsévié Motors', 'entreprise', 'Ako K.', '+228 95 68 79 80', 'contact@tsevemotors.tg', 'Tsévié', $afi],
            ['Maison Kékéli', 'entreprise', 'Kékéli A.', '+228 96 79 80 91', 'contact@kekeli.tg', 'Lomé', $kossi],
        ];

        foreach ($clients as $index => $donnees) {
            [$nom, $type, $contact, $telephone, $email, $ville, $transitaire] = $donnees;

            Client::firstOrCreate(
                ['code' => sprintf('CLI-2027-%03d', $index + 1)],
                [
                    'agence_id' => $agence->id,
                    'transitaire_id' => $transitaire->id,
                    'nom' => $nom,
                    'type' => $type,
                    'email' => $email,
                    'telephone' => $telephone,
                    'whatsapp' => $telephone,
                    'adresse' => 'Zone commerciale, '.$ville,
                    'ville' => $ville,
                    'pays' => str_contains($email, '.bj') ? 'Bénin' : 'Togo',
                    'contact_nom' => $contact,
                    'contact_telephone' => $telephone,
                    'contact_email' => $email,
                    'statut' => 'actif',
                ]
            );
        }

        // --- Comptes portail : actif / en attente / expiré -------------------

        $adjo = Client::where('nom', 'Société Adjo Import')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'client@jalon.tg'],
            [
                'agence_id' => $agence->id,
                'client_id' => $adjo->id,
                'nom' => 'Adjo K.',
                'telephone' => '+228 90 12 34 56',
                'role' => RoleUtilisateur::Client,
                'password' => Hash::make('password'),
                'statut_activation' => StatutActivation::Actif,
                'poste' => null,
                'email_verified_at' => now(),
            ]
        );

        // Invitation en attente (7 jours) pour Komia Distribution.
        $komia = Client::where('nom', 'Komia Distribution')->firstOrFail();
        $this->inviter($agence, $komia, $afi, now()->addDays(7));

        // Invitation expirée pour Ets Ayité et Fils.
        $ayite = Client::where('nom', 'Ets Ayité et Fils')->firstOrFail();
        $this->inviter($agence, $ayite, $afi, now()->subDays(2));
    }

    private function inviter(Agence $agence, Client $client, User $auteur, Carbon $expiration): void
    {
        $utilisateur = User::firstOrCreate(
            ['email' => $client->contact_email ?? Str::slug($client->nom).'@exemple.tg'],
            [
                'agence_id' => $agence->id,
                'client_id' => $client->id,
                'nom' => $client->contact_nom ?? $client->nom,
                'telephone' => $client->telephone,
                'role' => RoleUtilisateur::Client,
                'password' => null,
                'statut_activation' => $expiration->isPast()
                    ? StatutActivation::Expire
                    : StatutActivation::EnAttente,
            ]
        );

        Invitation::create([
            'agence_id' => $agence->id,
            'user_id' => $utilisateur->id,
            'client_id' => $client->id,
            'email' => $utilisateur->email,
            'nom' => $utilisateur->nom,
            'role' => RoleUtilisateur::Client,
            'token' => Str::random(64),
            'expires_at' => $expiration,
            'invited_by' => $auteur->id,
        ]);
    }
}
