<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Client;
use App\Models\Conteneur;
use App\Models\Cout;
use App\Models\Document;
use App\Models\Dossier;
use App\Models\HistoriqueStatut;
use App\Models\JalonConfig;
use App\Models\Regime;
use App\Models\User;
use App\Services\FranchiseService;
use App\Services\ReferenceService;
use Dompdf\Dompdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DossierSeeder extends Seeder
{
    private string $pdfDemo = '';

    public function run(): void
    {
        $agence = Agence::firstOrFail();
        $afi = User::where('email', 'agent@jalon.tg')->firstOrFail();
        $kossi = User::where('email', 'admin@jalon.tg')->firstOrFail();
        $clients = Client::pluck('id', 'nom');
        $regimes = Regime::with('documentsRequis')->get()->keyBy('code');

        $this->pdfDemo = $this->genererPdfDemo();

        $dossiers = $this->donnees($afi, $kossi);

        foreach ($dossiers as $donnees) {
            $clientId = $clients[$donnees['client']] ?? $clients->first();
            $regime = $regimes[$donnees['regime']];

            $dossier = Dossier::firstOrCreate(
                ['reference' => $donnees['ref']],
                [
                    'agence_id' => $agence->id,
                    'client_id' => $clientId,
                    'transitaire_id' => $donnees['auteur']->id,
                    'regime_id' => $regime->id,
                    'code_suivi' => (new ReferenceService)->codeSuivi(),
                    'ligne_maritime' => $donnees['ligne'],
                    'navire' => $donnees['navire'] ?? null,
                    'numero_bl' => $donnees['bl'] ?? null,
                    'nature_marchandise' => $donnees['nature'],
                    'date_arrivee_prevue' => isset($donnees['arrivee_prevue'])
                        ? today()->subDays($donnees['arrivee_prevue'])
                        : null,
                    'jalon_actuel' => $donnees['jalon'],
                    'brouillon' => $donnees['brouillon'] ?? false,
                    'marge_previsionnelle' => $donnees['previsionnel'] ?? 0,
                    'date_ouverture' => today()->subDays($donnees['jours']),
                    'date_cloture' => $donnees['jalon'] >= 9 ? today()->subDays(max(1, (int) ($donnees['jours'] / 4))) : null,
                ]
            );

            // Les dates de création sont forcées pour que les graphiques soient réalistes.
            $dossier->forceFill([
                'created_at' => today()->subDays($donnees['jours']),
                'updated_at' => today()->subDays(max(0, $donnees['jours'] - 2)),
            ])->saveQuietly();

            $this->historique($dossier, $donnees, $afi, $kossi);
            $this->conteneurs($dossier, $donnees, $regime);
            $this->documents($dossier, $donnees, $regime, $afi);
            $this->couts($dossier, $donnees, $afi);

            // Les dossiers clients restent rattachés à leur créateur pour le suivi.
            if ($dossier->client && $donnees['client'] === 'Société Adjo Import') {
                $dossier->client->update(['transitaire_id' => $donnees['auteur']->id]);
            }
        }
    }

    /**
     * Jeu de données : 24 dossiers dont les 7 nommés dans les maquettes.
     */
    private function donnees(User $afi, User $kossi): array
    {
        return [
            ['ref' => 'JL-2027-0011', 'client' => 'Ets Dossou & Frères', 'regime' => 'T1', 'jalon' => 9, 'jours' => 170, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Textiles et confection', 'navire' => 'MSC Katrine', 'bl' => 'MEDULOM771204', 'previsionnel' => 300000, 'conteneur' => ['numero' => 'MSCU7712043', 'type' => '40HC', 'arrivee' => 130, 'statut' => 'restitue']],
            ['ref' => 'JL-2027-0012', 'client' => 'Togo Textile SA', 'regime' => 'C100', 'jalon' => 9, 'jours' => 160, 'auteur' => $kossi, 'ligne' => 'Maersk', 'nature' => 'Machines à coudre industrielles', 'navire' => 'Maersk Lomé', 'bl' => 'MAEU2231904', 'previsionnel' => 420000, 'conteneur' => ['numero' => 'MRKU5501234', 'type' => '20DV', 'arrivee' => 120, 'statut' => 'restitue']],
            ['ref' => 'JL-2027-0013', 'client' => 'Hôtel du Golfe', 'regime' => 'C100', 'jalon' => 7, 'jours' => 150, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Mobilier hôtelier', 'bl' => 'MEDULOM778830', 'previsionnel' => 180000, 'conteneur' => ['numero' => 'MSCU6602211', 'type' => '40DV', 'arrivee' => 2]],
            ['ref' => 'JL-2027-0014', 'client' => 'Sarl Bénin Traders', 'regime' => 'T1', 'jalon' => 6, 'jours' => 145, 'auteur' => $afi, 'ligne' => 'CMA CGM', 'nature' => 'Riz parfumé en sacs', 'bl' => 'CMDU7721903', 'previsionnel' => 210000, 'conteneur' => ['numero' => 'CMAU4412009', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0015', 'client' => 'Kpalimé Cacao Coop', 'regime' => 'RX', 'jalon' => 9, 'jours' => 135, 'auteur' => $kossi, 'ligne' => 'Hapag-Lloyd', 'nature' => 'Fèves de cacao', 'bl' => 'HLCU9902118', 'previsionnel' => 150000, 'conteneur' => ['numero' => 'HLCU1120345', 'type' => '20DV', 'arrivee' => 80, 'statut' => 'restitue']],
            ['ref' => 'JL-2027-0016', 'client' => 'Nana Assurances', 'regime' => 'C100', 'jalon' => 3, 'jours' => 125, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Fournitures de bureau', 'bl' => 'MEDULOM781002', 'previsionnel' => 95000, 'conteneur' => ['numero' => 'MSCU7789023', 'type' => '20DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0017', 'client' => 'Sotrabo Agro', 'regime' => 'C100', 'jalon' => 8, 'jours' => 118, 'auteur' => $afi, 'ligne' => 'Maersk', 'nature' => 'Engrais NPK', 'bl' => 'MAEU3312098', 'previsionnel' => 260000, 'conteneur' => ['numero' => 'MRKU8890122', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0018', 'client' => 'Lomé Beach Resort', 'regime' => 'C100', 'jalon' => 4, 'jours' => 112, 'auteur' => $afi, 'ligne' => 'CMA CGM', 'nature' => 'Équipements de cuisine', 'bl' => 'CMDU8812340', 'previsionnel' => 130000, 'conteneur' => ['numero' => 'CMAU6678120', 'type' => '20DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0019', 'client' => 'Ets Foli K.', 'regime' => 'EN', 'jalon' => 2, 'jours' => 105, 'auteur' => $kossi, 'ligne' => 'MSC', 'nature' => 'Pièces détachées automobiles', 'bl' => 'MEDULOM782211', 'previsionnel' => 70000, 'conteneur' => ['numero' => 'MSCU2238901', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0020', 'client' => 'Sika Matériaux', 'regime' => 'C100', 'jalon' => 9, 'jours' => 98, 'auteur' => $afi, 'ligne' => 'Hapag-Lloyd', 'nature' => 'Ciment et matériaux de construction', 'navire' => 'MSC Katrina', 'bl' => 'HLCU3345678', 'previsionnel' => 390000, 'conteneur' => ['numero' => 'HLCU3345678', 'type' => '40HC', 'arrivee' => 50, 'statut' => 'restitue']],
            ['ref' => 'JL-2027-0021', 'client' => 'Tsévié Motors', 'regime' => 'C100', 'jalon' => 8, 'jours' => 92, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Véhicules utilitaires', 'bl' => 'MEDULOM783344', 'previsionnel' => 240000, 'conteneur' => ['numero' => 'MSCU9910334', 'type' => '40DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0022', 'client' => 'Maison Kékéli', 'regime' => 'C100', 'jalon' => 7, 'jours' => 88, 'auteur' => $kossi, 'ligne' => 'CMA CGM', 'nature' => 'Produits cosmétiques', 'bl' => 'CMDU8823451', 'previsionnel' => 110000, 'conteneur' => ['numero' => 'CMAU5589023', 'type' => '20DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0023', 'client' => 'Afi Cosmétiques', 'regime' => 'C100', 'jalon' => 9, 'jours' => 82, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Produits de soin', 'bl' => 'MEDULOM784455', 'previsionnel' => 160000, 'conteneur' => ['numero' => 'MSCU4456712', 'type' => '20DV', 'arrivee' => 30, 'statut' => 'restitue']],
            ['ref' => 'JL-2027-0024', 'client' => 'Komia Distribution', 'regime' => 'T1', 'jalon' => 5, 'jours' => 76, 'auteur' => $afi, 'ligne' => 'Maersk', 'nature' => 'Huile de palme', 'bl' => 'MAEU4412789', 'previsionnel' => 205000, 'conteneur' => ['numero' => 'MRKU2234510', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0025', 'client' => 'Ets Ayité et Fils', 'regime' => 'C100', 'jalon' => 4, 'jours' => 55, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Peintures et vernis', 'bl' => 'MEDULOM785566', 'previsionnel' => 88000, 'conteneur' => ['numero' => 'MSCU6634021', 'type' => '20DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0026', 'client' => 'Mawuli Import', 'regime' => 'C100', 'jalon' => 3, 'jours' => 50, 'auteur' => $kossi, 'ligne' => 'CMA CGM', 'nature' => 'Jouets et articles de sport', 'bl' => 'CMDU8834512', 'previsionnel' => 65000, 'conteneur' => ['numero' => 'CMAU7712234', 'type' => '20DV', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0027', 'client' => 'Kodjo Négoce', 'regime' => 'C100', 'jalon' => 4, 'jours' => 14, 'auteur' => $afi, 'ligne' => 'Maersk', 'nature' => 'Riz et denrées alimentaires', 'bl' => 'MAEU5523401', 'previsionnel' => 175000, 'arrivee_prevue' => -1, 'conteneur' => ['numero' => 'MSKU9012345', 'type' => '20DV', 'arrivee' => 12, 'sortie' => 6, 'statut' => 'sorti']],
            ['ref' => 'JL-2027-0028', 'client' => 'Komia Distribution', 'regime' => 'C100', 'jalon' => 8, 'jours' => 3, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Boissons et conserves', 'bl' => 'MEDULOM787788', 'previsionnel' => 300000, 'conteneur' => ['numero' => 'TGHU8830214', 'type' => '20DV', 'arrivee' => 3]],
            ['ref' => 'JL-2027-0029', 'client' => 'Afi Cosmétiques', 'regime' => 'C100', 'jalon' => 8, 'jours' => 7, 'auteur' => $afi, 'ligne' => 'MSC', 'nature' => 'Cosmétiques et parfums', 'bl' => 'MEDULOM787901', 'previsionnel' => 210000, 'conteneur' => ['numero' => 'MRKU6619032', 'type' => '40HC', 'arrivee' => 2]],
            ['ref' => 'JL-2027-0030', 'client' => 'Sika Matériaux', 'regime' => 'C100', 'jalon' => 2, 'jours' => 36, 'auteur' => $kossi, 'ligne' => 'Maersk', 'nature' => 'Tôles et profilés métalliques', 'bl' => 'MAEU6612389', 'previsionnel' => 140000, 'conteneur' => ['numero' => 'MRKU1123098', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0031', 'client' => 'Société Adjo Import', 'regime' => 'C100', 'jalon' => 8, 'jours' => 9, 'auteur' => $afi, 'ligne' => 'MSC', 'navire' => 'MSC Katrine', 'nature' => 'Produits agroalimentaires & conserves', 'bl' => 'MEDULOM890241', 'previsionnel' => 140000, 'conteneur' => ['numero' => 'MSCU4521870', 'type' => '40HC', 'arrivee' => 4]],
            ['ref' => 'JL-2027-0032', 'client' => 'Togo Textile SA', 'regime' => 'T1', 'jalon' => 6, 'jours' => 5, 'auteur' => $kossi, 'ligne' => 'CMA CGM', 'nature' => 'Tissus imprimés', 'bl' => 'CMDU8845623', 'previsionnel' => 190000, 'conteneur' => ['numero' => 'CMAU9901234', 'type' => '40HC', 'arrivee' => 1]],
            ['ref' => 'JL-2027-0033', 'client' => 'Mawuli Import', 'regime' => 'C100', 'jalon' => 1, 'jours' => 30, 'auteur' => $kossi, 'ligne' => 'MSC', 'nature' => 'Marchandises diverses', 'bl' => 'MEDULOM891377', 'previsionnel' => 0, 'brouillon' => true],
            ['ref' => 'JL-2027-0034', 'client' => 'Ets Ayité et Fils', 'regime' => 'C100', 'jalon' => 8, 'jours' => 25, 'auteur' => $afi, 'ligne' => 'CMA CGM', 'nature' => 'Matériel électrique', 'bl' => 'CMDU8856712', 'previsionnel' => 250000, 'conteneur' => ['numero' => 'CMAU2276405', 'type' => '40DV', 'arrivee' => 2]],
        ];
    }

    private function historique(Dossier $dossier, array $donnees, User $afi, User $kossi): void
    {
        if ($dossier->historique()->exists()) {
            return;
        }

        $debut = Carbon::parse($dossier->created_at);
        $etapes = max(1, $donnees['jalon'] - 1);
        $intervalle = (int) floor($donnees['jours'] / max(1, $etapes + 1));

        for ($jalon = 1; $jalon <= $donnees['jalon']; $jalon++) {
            $date = $debut->copy()->addDays($intervalle * ($jalon - 1));

            if ($date->isFuture()) {
                $date = Carbon::today();
            }

            $entree = HistoriqueStatut::create([
                'dossier_id' => $dossier->id,
                'user_id' => ($jalon % 2 === 0 ? $kossi : $afi)->id,
                'jalon' => $jalon,
                'jalon_precedent' => $jalon > 1 ? $jalon - 1 : null,
                'action' => $jalon === 1 ? 'creation' : 'avance',
                'commentaire' => $jalon === 1
                    ? 'Création du dossier et enregistrement du client.'
                    : 'Passage au jalon '.$jalon.' : '.JalonConfig::tous()->firstWhere('ordre', $jalon)?->nom,
                'date_passage' => $date->toDateString(),
            ]);
            $entree->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
        }
    }

    private function conteneurs(Dossier $dossier, array $donnees, Regime $regime): void
    {
        if (! isset($donnees['conteneur'])) {
            return;
        }

        $c = $donnees['conteneur'];
        $arrivee = today()->subDays($c['arrivee']);

        $conteneur = Conteneur::firstOrCreate(
            ['numero' => $c['numero']],
            [
                'dossier_id' => $dossier->id,
                'type' => $c['type'],
                'taille' => str_starts_with($c['type'], '20') ? '20 pieds' : '40 pieds',
                'statut' => $c['statut'] ?? 'au_port',
                'date_arrivee' => $arrivee,
                'date_sortie' => isset($c['sortie']) ? today()->subDays($c['sortie']) : null,
                'date_restitution' => ($c['statut'] ?? '') === 'restitue' ? today()->subDays($c['arrivee'] - 10) : null,
                'ligne_maritime' => $donnees['ligne'],
                'numero_plomb' => 'PLB'.random_int(100000, 999999),
                'poids_kg' => [20000, 24000, 18500, 26500][array_rand([20000, 24000, 18500, 26500])],
            ]
        );

        (new FranchiseService)->appliquer($conteneur, $donnees['ligne']);

        // Événements du conteneur (détail).
        if ($conteneur->evenements()->doesntExist()) {
            $conteneur->evenements()->createMany([
                ['type' => 'arrivee', 'libelle' => 'Arrivée au port de Lomé', 'date_evenement' => $arrivee, 'description' => 'Conteneur débarqué du navire.'],
                ['type' => 'franchise', 'libelle' => 'Fin de franchise calculée', 'date_evenement' => $conteneur->fin_franchise, 'description' => 'Délai de '.$regime->code.' / '.$donnees['ligne'].' appliqué.'],
            ]);
        }
    }

    private function documents(Dossier $dossier, array $donnees, Regime $regime, User $auteur): void
    {
        if ($dossier->documents()->exists()) {
            return;
        }

        $requis = $regime->documentsRequis;
        $nombre = match (true) {
            $donnees['ref'] === 'JL-2027-0031' => 6,
            $donnees['jalon'] >= 8 => min(6, $requis->count()),
            $donnees['jalon'] >= 5 => 4,
            $donnees['jalon'] >= 3 => 3,
            default => 1,
        };

        $valides = $donnees['ref'] === 'JL-2027-0031' ? 3 : min($nombre, max(1, $donnees['jalon'] - 2));

        foreach ($requis->take($nombre)->values() as $index => $piece) {
            $chemin = 'documents/'.$dossier->id.'/'.Str::slug($piece->libelle).'-'.Str::random(6).'.pdf';

            Storage::disk('local')->put($chemin, $this->pdfDemo);

            Document::create([
                'dossier_id' => $dossier->id,
                'type' => $piece->libelle,
                'nom_original' => Str::slug($piece->libelle).'.pdf',
                'chemin' => $chemin,
                'mime' => 'application/pdf',
                'taille' => strlen($this->pdfDemo),
                'valide' => $index < $valides,
                'valide_par' => $index < $valides ? $auteur->id : null,
                'date_validation' => $index < $valides ? now()->subDays(2) : null,
                'visible_client' => true,
                'ajoute_par' => $auteur->id,
            ]);
        }
    }

    private function couts(Dossier $dossier, array $donnees, User $auteur): void
    {
        if ($dossier->couts()->exists()) {
            return;
        }

        $couts = match ($donnees['ref']) {
            'JL-2027-0031' => [
                ['Honoraires de transit', 120000, 'transit'],
                ['Droits et taxes douaniers', 210000, 'douane'],
                ['Transport et livraison', 86400, 'transport'],
            ],
            'JL-2027-0028' => [
                ['Frais portuaires', 260000, 'manutention'],
                ['Droits et taxes douaniers', 720000, 'douane'],
                ['Transport et livraison', 165000, 'transport'],
            ],
            'JL-2027-0034' => [
                ['Honoraires de transit', 150000, 'transit'],
                ['Droits et taxes douaniers', 450000, 'douane'],
                ['Manutention portuaire', 132000, 'manutention'],
            ],
            'JL-2027-0020' => [
                ['Honoraires de transit', 180000, 'transit'],
                ['Droits et taxes douaniers', 300000, 'douane'],
                ['Transport et livraison', 130000, 'transport'],
            ],
            default => [],
        };

        foreach ($couts as $index => [$libelle, $montant, $categorie]) {
            Cout::create([
                'dossier_id' => $dossier->id,
                'libelle' => $libelle,
                'categorie' => $categorie,
                'montant' => $montant,
                'date' => Carbon::parse($dossier->created_at)->addDays($index + 1),
                'fournisseur' => null,
                'saisi_par' => $auteur->id,
            ]);
        }
    }

    /** Un PDF de démonstration, généré une seule fois et réutilisé. */
    private function genererPdfDemo(): string
    {
        try {
            $dompdf = new Dompdf(['isRemoteEnabled' => false]);
            $dompdf->loadHtml(
                '<html><body style="font-family: sans-serif; padding: 40px;">'
                .'<h1 style="color:#C9460E;">Document de démonstration</h1>'
                .'<p>Jalon — ERP transit et douane · Port de Lomé (Togo).</p>'
                .'<p>Ce fichier fictif est fourni avec le jeu de données de démonstration.</p>'
                .'</body></html>'
            );
            $dompdf->setPaper('A4');
            $dompdf->render();

            return $dompdf->output();
        } catch (\Throwable $e) {
            return "%PDF-1.4\n% Document de démonstration Jalon\n%%EOF\n";
        }
    }
}
