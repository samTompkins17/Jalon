<?php

namespace Database\Seeders;

use App\Enums\ModePaiement;
use App\Models\Agence;
use App\Models\Cout;
use App\Models\Dossier;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\User;
use App\Services\FactureService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FactureSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrFail();
        $yao = User::where('email', 'comptable@jalon.tg')->firstOrFail();

        $debutMois = Carbon::now()->startOfMonth();
        $jourMax = max(0, Carbon::now()->day - 1);

        // Les 7 dernières factures tombent dans le mois courant : le CA du tableau
        // de bord affiche ainsi 9 120 000 FCFA, comme sur la maquette.
        $factures = [
            // ---- Historique (le mois précédent sert de référence) --------
            [
                'numero' => 'FA-2027-0001', 'dossier' => 'JL-2027-0023',
                'date' => Carbon::now()->subMonths(5)->startOfMonth()->addDays(4),
                'echeance' => Carbon::now()->subMonths(5)->startOfMonth()->addDays(34),
                'lignes' => [['Honoraires de transit', 900000], ['Droits et taxes douaniers', 1200000], ['Transport et livraison', 300000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0002', 'dossier' => 'JL-2027-0022',
                'date' => Carbon::now()->subMonths(4)->startOfMonth()->addDays(9),
                'echeance' => Carbon::now()->subMonths(4)->startOfMonth()->addDays(39),
                'lignes' => [['Honoraires de transit', 1100000], ['Droits et taxes douaniers', 1600000], ['Transport et livraison', 400000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0003', 'dossier' => 'JL-2027-0015',
                'date' => Carbon::now()->subMonths(3)->startOfMonth()->addDays(6),
                'echeance' => Carbon::now()->subMonths(3)->startOfMonth()->addDays(36),
                'lignes' => [['Honoraires de transit', 1800000], ['Droits et taxes douaniers', 2200000], ['Transport et livraison', 500000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0004', 'dossier' => 'JL-2027-0013',
                'date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(12),
                'echeance' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(42),
                'lignes' => [['Honoraires de transit', 2000000], ['Droits et taxes douaniers', 2700000], ['Transport et livraison', 500000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0005', 'dossier' => 'JL-2027-0011',
                'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(8),
                'echeance' => Carbon::now()->subMonth()->startOfMonth()->addDays(38),
                'lignes' => [['Honoraires de transit', 3100000], ['Droits et taxes douaniers', 4200000], ['Transport et livraison', 813900]],
                'paye' => 'total',
            ],

            // ---- Mois courant (CA = 9 120 000 FCFA) ---------------------------
            [
                'numero' => 'FA-2027-0006', 'dossier' => 'JL-2027-0011',
                'date' => $debutMois->copy()->addDays(min(2, $jourMax)),
                'echeance' => $debutMois->copy()->addDays(min(32, $jourMax + 30)),
                'lignes' => [['Honoraires de transit', 600000], ['Droits et taxes douaniers', 800000], ['Transport et livraison', 373600]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0007', 'dossier' => 'JL-2027-0034',
                'date' => $debutMois->copy()->addDays(min(3, $jourMax)),
                'echeance' => $debutMois->copy()->addDays(min(33, $jourMax + 30)),
                'lignes' => [['Honoraires de transit', 300000], ['Droits et taxes douaniers', 500000], ['Manutention portuaire', 200000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0008', 'dossier' => 'JL-2027-0021',
                'date' => $debutMois->copy()->addDays(min(4, $jourMax)),
                'echeance' => $debutMois->copy()->addDays(min(34, $jourMax + 30)),
                'lignes' => [['Honoraires de transit', 450000], ['Droits et taxes douaniers', 700000], ['Transport et livraison', 200000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0009', 'dossier' => 'JL-2027-0017',
                'date' => $debutMois->copy()->addDays(min(5, $jourMax)),
                'echeance' => $debutMois->copy()->addDays(min(35, $jourMax + 30)),
                'lignes' => [['Honoraires de transit', 480000], ['Droits et taxes douaniers', 1200000], ['Transport et livraison', 300000]],
                'paye' => 1000000,
            ],
            [
                'numero' => 'FA-2027-0010', 'dossier' => 'JL-2027-0020',
                'date' => $debutMois->copy()->addDays(min(6, $jourMax)),
                'echeance' => $debutMois->copy()->addDays(min(36, $jourMax + 30)),
                'lignes' => [['Honoraires de transit', 250000], ['Droits et taxes douaniers', 600000], ['Transport et livraison', 150000]],
                'paye' => 'total',
            ],
            [
                'numero' => 'FA-2027-0011', 'dossier' => 'JL-2027-0028',
                'date' => $debutMois->copy()->addDays(min(7, $jourMax)),
                'echeance' => Carbon::now()->addDays(15),
                'lignes' => [['Honoraires de transit', 350000], ['Droits et taxes douaniers', 850000], ['Manutention portuaire', 250000]],
                'paye' => 0,
            ],
            [
                'numero' => 'FA-2027-0012', 'dossier' => 'JL-2027-0031',
                'date' => $debutMois->copy()->addDays(min(8, $jourMax)),
                'echeance' => Carbon::now()->addDays(23),
                'lignes' => [['Honoraires de transit', 150000], ['Frais portuaires', 220000], ['Transport', 110000]],
                'tva' => 18,
                'paye' => 200000,
            ],
        ];

        $service = new FactureService;

        foreach ($factures as $donnees) {
            $dossier = Dossier::where('reference', $donnees['dossier'])->firstOrFail();

            $facture = Facture::firstOrCreate(
                ['numero' => $donnees['numero']],
                [
                    'agence_id' => $agence->id,
                    'client_id' => $dossier->client_id,
                    'dossier_id' => $dossier->id,
                    'date_facture' => $donnees['date'],
                    'date_echeance' => $donnees['echeance'],
                    'tva_taux' => $donnees['tva'] ?? 0,
                    'emise_par' => $yao->id,
                ]
            );

            if ($facture->lignes()->exists()) {
                continue;
            }

            foreach ($donnees['lignes'] as $ordre => [$designation, $montant]) {
                $facture->lignes()->create([
                    'designation' => $designation,
                    'quantite' => 1,
                    'prix_unitaire' => $montant,
                    'montant' => $montant,
                    'ordre' => $ordre + 1,
                ]);
            }

            $facture->load('lignes');
            $service->recalculer($facture);

            $montantPaye = $donnees['paye'] === 'total' ? $facture->total : (float) $donnees['paye'];

            if ($montantPaye > 0) {
                Paiement::create([
                    'facture_id' => $facture->id,
                    'montant' => $montantPaye,
                    'mode' => ModePaiement::Virement,
                    'date_paiement' => Carbon::parse($donnees['date'])->addDays($donnees['paye'] === 'total' ? 6 : 3),
                    'reference' => 'VIR-2027-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                    'enregistre_par' => $yao->id,
                ]);
            }

            $service->recalculer($facture->fresh(['lignes', 'paiements']));
        }

        $this->completerCouts($yao);
    }

    /**
     * Pour les dossiers facturés sans coûts saisis, on génère une structure de
     * coûts réaliste (environ 78 % du facturé) afin que la marge soit crédible.
     */
    private function completerCouts(User $auteur): void
    {
        Dossier::whereHas('factures')
            ->whereDoesntHave('couts')
            ->with('factures')
            ->get()
            ->each(function (Dossier $dossier) use ($auteur) {
                $total = (float) $dossier->factures->sum('total');
                $date = $dossier->factures->min('date_facture') ?? now();

                $repartition = [
                    ['Honoraires de transit', 0.2, 'transit'],
                    ['Droits et taxes douaniers', 0.4, 'douane'],
                    ['Transport et livraison', 0.18, 'transport'],
                ];

                foreach ($repartition as [$libelle, $part, $categorie]) {
                    Cout::create([
                        'dossier_id' => $dossier->id,
                        'libelle' => $libelle,
                        'categorie' => $categorie,
                        'montant' => round($total * $part, 2),
                        'date' => $date,
                        'saisi_par' => $auteur->id,
                    ]);
                }
            });
    }
}
