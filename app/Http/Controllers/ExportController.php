<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Conteneur;
use App\Models\Dossier;
use App\Models\Facture;
use App\Models\JournalActivite;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports CSV et rapports de l'agence. Les fichiers sont générés à la volée
 * (séparateur « ; », BOM UTF-8) pour rester lisibles dans Excel en français.
 */
class ExportController extends Controller
{
    public const TYPES = [
        'dossiers' => 'Dossiers',
        'factures' => 'Factures',
        'clients' => 'Clients',
        'conteneurs' => 'Conteneurs',
        'journal' => 'Journal d\'activité',
    ];

    public function index(Request $request): View
    {
        $this->autoriser($request);

        $agenceId = $request->user()->agence_id;
        $periode = (string) $request->query('periode', '90');
        $debut = $this->debut($periode);

        $factures = Facture::where('agence_id', $agenceId)
            ->when($debut, fn ($requete) => $requete->whereDate('date_facture', '>=', $debut));

        $ca = (float) (clone $factures)->sum('total');
        $encaisse = (float) Paiement::whereIn('facture_id', (clone $factures)->select('id'))
            ->when($debut, fn ($requete) => $requete->whereDate('date_paiement', '>=', $debut))
            ->sum('montant');

        $dossiers = Dossier::where('agence_id', $agenceId)
            ->when($debut, fn ($requete) => $requete->whereDate('date_ouverture', '>=', $debut));

        return view('exports.index', [
            'periode' => $periode,
            'types' => self::TYPES,
            'ca' => $ca,
            'encaisse' => $encaisse,
            'impaye' => max(0, $ca - $encaisse),
            'nombreDossiers' => (clone $dossiers)->count(),
            'dossiersLivres' => (clone $dossiers)->where('jalon_actuel', '>=', 9)->count(),
            'conteneursAuPort' => Conteneur::whereHas('dossier', fn ($dossier) => $dossier->where('agence_id', $agenceId))
                ->where('statut', 'au_port')
                ->count(),
            'journal' => JournalActivite::where('agence_id', $agenceId)
                ->with(['user', 'dossier'])
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function csv(Request $request, string $type): StreamedResponse
    {
        $this->autoriser($request);

        abort_unless(array_key_exists($type, self::TYPES), 404);

        $agenceId = $request->user()->agence_id;
        $debut = $this->debut((string) $request->query('periode', 'tous'));

        [$entetes, $lignes] = match ($type) {
            'dossiers' => $this->dossiers($agenceId, $debut),
            'factures' => $this->factures($agenceId, $debut),
            'clients' => $this->clients($agenceId),
            'conteneurs' => $this->conteneurs($agenceId),
            default => $this->journal($agenceId),
        };

        $nom = 'jalon-'.$type.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($entetes, $lignes) {
            $sortie = fopen('php://output', 'w');

            // BOM UTF-8 : accents corrects dans Excel.
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, $entetes, ';');

            foreach ($lignes as $ligne) {
                fputcsv($sortie, $ligne, ';');
            }

            fclose($sortie);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------ Sources

    private function dossiers(int $agenceId, ?Carbon $debut): array
    {
        $dossiers = Dossier::where('agence_id', $agenceId)
            ->with(['client', 'regime', 'transitaire', 'conteneurs'])
            ->when($debut, fn ($requete) => $requete->whereDate('date_ouverture', '>=', $debut))
            ->latest('date_ouverture')
            ->get();

        $entetes = ['Référence', 'Client', 'Régime', 'Jalon', 'État', 'Ouvert le', 'Responsable', 'Conteneurs', 'Marge (FCFA)'];

        $lignes = $dossiers->map(fn (Dossier $dossier) => [
            $dossier->reference,
            $dossier->client?->nom,
            $dossier->regime?->nom,
            $dossier->jalon_actuel.' / 9',
            $dossier->etat_label,
            $dossier->date_ouverture?->format('d/m/Y'),
            $dossier->transitaire?->nom,
            $dossier->conteneurs->pluck('numero')->implode(' '),
            number_format($dossier->marge_reelle, 0, ',', ' '),
        ]);

        return [$entetes, $lignes];
    }

    private function factures(int $agenceId, ?Carbon $debut): array
    {
        $factures = Facture::where('agence_id', $agenceId)
            ->with(['client', 'dossier'])
            ->withSum('paiements', 'montant')
            ->when($debut, fn ($requete) => $requete->whereDate('date_facture', '>=', $debut))
            ->latest('date_facture')
            ->get();

        $entetes = ['Numéro', 'Client', 'Dossier', 'Émise le', 'Échéance', 'Total TTC', 'Payé', 'Solde', 'Statut'];

        $lignes = $factures->map(fn (Facture $facture) => [
            $facture->numero,
            $facture->client?->nom,
            $facture->dossier?->reference,
            $facture->date_facture?->format('d/m/Y'),
            $facture->date_echeance?->format('d/m/Y'),
            number_format($facture->total, 0, ',', ' '),
            number_format($facture->montant_paye, 0, ',', ' '),
            number_format($facture->solde, 0, ',', ' '),
            $facture->statut->label(),
        ]);

        return [$entetes, $lignes];
    }

    private function clients(int $agenceId): array
    {
        $clients = Client::where('agence_id', $agenceId)->withCount('dossiers')->orderBy('nom')->get();

        $entetes = ['Code', 'Nom', 'Type', 'Contact', 'Téléphone', 'E-mail', 'Ville', 'Dossiers', 'Solde dû (FCFA)'];

        $lignes = $clients->map(fn (Client $client) => [
            $client->code,
            $client->nom,
            ucfirst($client->type),
            $client->contact_nom,
            $client->telephone,
            $client->email,
            $client->ville,
            $client->dossiers_count,
            number_format($client->solde_du, 0, ',', ' '),
        ]);

        return [$entetes, $lignes];
    }

    private function conteneurs(int $agenceId): array
    {
        $conteneurs = Conteneur::whereHas('dossier', fn ($dossier) => $dossier->where('agence_id', $agenceId))
            ->with('dossier.client')
            ->orderBy('numero')
            ->get();

        $entetes = ['Numéro', 'Dossier', 'Client', 'Type', 'Statut', 'Arrivée', 'Fin de franchise', 'Sortie', 'Restitution'];

        $lignes = $conteneurs->map(fn (Conteneur $conteneur) => [
            $conteneur->numero_formate,
            $conteneur->dossier?->reference,
            $conteneur->dossier?->client?->nom,
            $conteneur->type,
            $conteneur->statut->label(),
            $conteneur->date_arrivee?->format('d/m/Y'),
            $conteneur->fin_franchise?->format('d/m/Y'),
            $conteneur->date_sortie?->format('d/m/Y'),
            $conteneur->date_restitution?->format('d/m/Y'),
        ]);

        return [$entetes, $lignes];
    }

    private function journal(int $agenceId): array
    {
        $entrees = JournalActivite::where('agence_id', $agenceId)
            ->with(['user', 'dossier'])
            ->latest()
            ->limit(2000)
            ->get();

        $entetes = ['Date', 'Utilisateur', 'Action', 'Détail', 'Dossier'];

        $lignes = $entrees->map(fn (JournalActivite $entree) => [
            $entree->created_at?->format('d/m/Y H:i'),
            $entree->user?->nom,
            $entree->action,
            $entree->description,
            $entree->dossier?->reference,
        ]);

        return [$entetes, $lignes];
    }

    // ------------------------------------------------------------------ Aides

    private function debut(string $periode): ?Carbon
    {
        $jours = match ((int) $periode) {
            7 => 7,
            30 => 30,
            90 => 90,
            365 => 365,
            default => null,
        };

        return $jours ? now()->subDays($jours)->startOfDay() : null;
    }

    private function autoriser(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role->value, ['administrateur', 'comptable'], true),
            403,
            'Accès réservé à la gestion.',
        );
    }
}
