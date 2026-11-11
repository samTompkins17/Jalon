<?php

namespace App\Http\Controllers;

use App\Enums\ModePaiement;
use App\Enums\StatutFacture;
use App\Models\Client;
use App\Models\Dossier;
use App\Models\Facture;
use App\Models\Paiement;
use App\Services\FactureService;
use App\Services\JournalService;
use App\Services\ReferenceService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FactureController extends Controller
{
    public function index(Request $request): View
    {
        $this->autoriser($request);

        $agenceId = $request->user()->agence_id;
        $recherche = trim((string) $request->query('q'));
        $statut = (string) $request->query('statut', 'toutes');

        $base = Facture::query()->where('agence_id', $agenceId);

        $compteurs = [
            'toutes' => (clone $base)->count(),
            'impayee' => (clone $base)->where('statut', StatutFacture::Impayee)->count(),
            'partielle' => (clone $base)->where('statut', StatutFacture::Partielle)->count(),
            'payee' => (clone $base)->where('statut', StatutFacture::Payee)->count(),
        ];

        $requete = (clone $base)->with(['client', 'dossier'])
            ->withSum('paiements', 'montant');

        if ($recherche !== '') {
            $motif = '%'.$recherche.'%';

            $requete->where(function ($sous) use ($motif) {
                $sous->where('numero', 'like', $motif)
                    ->orWhereHas('client', fn ($client) => $client->where('nom', 'like', $motif))
                    ->orWhereHas('dossier', fn ($dossier) => $dossier->where('reference', 'like', $motif));
            });
        }

        if (in_array($statut, ['impayee', 'partielle', 'payee'], true)) {
            $requete->where('statut', $statut);
        } else {
            $statut = 'toutes';
        }

        $factures = $requete->latest('date_facture')->latest('id')->paginate(8)->withQueryString();

        $selection = null;

        if ($request->filled('facture')) {
            $selection = Facture::where('agence_id', $agenceId)->where('numero', $request->query('facture'))->first();
        }

        $selection ??= $factures->first();

        if ($selection) {
            $selection->load(['lignes', 'paiements', 'client', 'dossier']);
        }

        return view('facturation.index', [
            'factures' => $factures,
            'selection' => $selection,
            'compteurs' => $compteurs,
            'statut' => $statut,
            'recherche' => $recherche,
            'modes' => ModePaiement::cases(),
        ]);
    }

    public function show(Request $request, Facture $facture): View
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $facture);

        $facture->load(['lignes', 'paiements.auteur', 'client', 'dossier', 'emetteur']);

        return view('facturation.show', [
            'facture' => $facture,
            'modes' => ModePaiement::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->autoriser($request);

        $agence = $request->user()->agence;
        $clientSelectionne = Client::where('agence_id', $agence->id)
            ->where('code', $request->query('client'))
            ->first();

        return view('facturation.formulaire', [
            'clients' => Client::where('agence_id', $agence->id)->orderBy('nom')->get(),
            'dossiers' => Dossier::where('agence_id', $agence->id)
                ->with('client')
                ->latest()
                ->get(),
            'numero' => (new ReferenceService)->apercu($agence, 'facture'),
            'tvaTaux' => (float) ($agence->tva_taux ?? 18),
            'clientSelectionne' => $clientSelectionne,
            'dossierSelectionne' => $request->query('dossier'),
        ]);
    }

    public function store(Request $request, ReferenceService $references, FactureService $service, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);

        $donnees = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'dossier_id' => ['required', 'exists:dossiers,id'],
            'date_facture' => ['required', 'date'],
            'date_echeance' => ['required', 'date', 'after_or_equal:date_facture'],
            'tva_taux' => ['required', 'numeric', 'min:0', 'max:30'],
            'remise' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:150'],
            'lignes.*.quantite' => ['required', 'numeric', 'min:0.01'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'brouillon' => ['nullable', 'boolean'],
        ], [
            'client_id.required' => 'Choisissez le client facturé.',
            'dossier_id.required' => 'Rattachez la facture à un dossier.',
            'date_facture.required' => 'Renseignez la date d\'émission.',
            'date_echeance.after_or_equal' => 'L\'échéance doit suivre la date d\'émission.',
            'lignes.required' => 'Ajoutez au moins une prestation.',
            'lignes.*.designation.required' => 'Chaque ligne doit avoir une désignation.',
        ]);

        $agence = $request->user()->agence;

        $client = Client::query()
            ->where('agence_id', $agence->id)
            ->whereKey($donnees['client_id'])
            ->first();

        $dossier = Dossier::query()
            ->where('agence_id', $agence->id)
            ->whereKey($donnees['dossier_id'])
            ->first();

        if (! $client) {
            throw ValidationException::withMessages([
                'client_id' => 'Ce client n\'appartient pas à votre agence.',
            ]);
        }

        if (! $dossier) {
            throw ValidationException::withMessages([
                'dossier_id' => 'Ce dossier n\'appartient pas à votre agence.',
            ]);
        }

        if ($dossier->client_id !== $client->id) {
            throw ValidationException::withMessages([
                'dossier_id' => 'Ce dossier est rattaché à un autre client : choisissez un dossier du client facturé.',
            ]);
        }

        $facture = DB::transaction(function () use ($donnees, $request, $agence, $references) {
            $facture = Facture::create([
                'agence_id' => $agence->id,
                'client_id' => $donnees['client_id'],
                'dossier_id' => $donnees['dossier_id'],
                'numero' => $references->facture($agence),
                'date_facture' => $donnees['date_facture'],
                'date_echeance' => $donnees['date_echeance'],
                'tva_taux' => $donnees['tva_taux'],
                'remise' => $donnees['remise'] ?? 0,
                'sous_total' => 0,
                'tva_montant' => 0,
                'total' => 0,
                'statut' => StatutFacture::Impayee,
                'notes' => $donnees['notes'] ?? null,
                'emise_par' => $request->user()->id,
            ]);

            foreach (array_values($donnees['lignes']) as $ordre => $ligne) {
                $facture->lignes()->create([
                    'designation' => $ligne['designation'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'montant' => round((float) $ligne['quantite'] * (float) $ligne['prix_unitaire']),
                    'ordre' => $ordre,
                ]);
            }

            return $facture;
        });

        $service->recalculer($facture->load('lignes', 'paiements'));

        $journal->enregistrer(
            'Émission facture',
            'Facture '.$facture->numero.' émise pour '.$facture->client?->nom.'.',
            $facture->dossier,
            $request->user(),
            $facture,
        );

        return redirect()
            ->route('facturation.show', $facture)
            ->with('succes', 'Facture '.$facture->numero.' enregistrée.');
    }

    public function paiement(Request $request, Facture $facture, FactureService $service, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $facture);

        $donnees = $request->validate([
            'montant' => ['required', 'numeric', 'min:1'],
            'mode' => ['required', Rule::enum(ModePaiement::class)],
            'date_paiement' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:60'],
        ], [
            'montant.required' => 'Renseignez le montant encaissé.',
            'montant.min' => 'Le montant doit être supérieur à zéro.',
            'mode.required' => 'Choisissez le mode de paiement.',
            'date_paiement.required' => 'Renseignez la date du règlement.',
        ]);

        $facture->loadSum('paiements', 'montant');
        $restant = max(0, $facture->total - (float) ($facture->paiements_sum_montant ?? 0));

        if ((float) $donnees['montant'] > $restant + 0.5) {
            return back()
                ->withInput()
                ->withErrors(['montant' => 'Le montant dépasse le solde restant de '.Format::nombre($restant).' FCFA.']);
        }

        Paiement::create([
            'facture_id' => $facture->id,
            'montant' => $donnees['montant'],
            'mode' => $donnees['mode'],
            'date_paiement' => $donnees['date_paiement'],
            'reference' => $donnees['reference'] ?? null,
            'enregistre_par' => $request->user()->id,
        ]);

        $service->rafraichirStatut($facture->fresh(['lignes', 'paiements']));

        $journal->enregistrer(
            'Paiement reçu',
            Format::nombre($donnees['montant']).' FCFA reçus sur la facture '.$facture->numero.'.',
            $facture->dossier,
            $request->user(),
            $facture,
        );

        return redirect()
            ->route('facturation.show', $facture)
            ->with('succes', 'Paiement enregistré.');
    }

    /** Version imprimable (impression navigateur ou « Enregistrer en PDF »). */
    public function imprimable(Request $request, Facture $facture): View
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $facture);

        $facture->load(['lignes', 'paiements', 'client', 'dossier', 'emetteur', 'agence']);

        return view('facturation.imprimable', [
            'facture' => $facture,
        ]);
    }

    private function autoriser(Request $request): void
    {
        abort_unless(
            $request->user()->peutFacturer() || $request->user()->permission('facturer'),
            403,
            'Accès réservé à la facturation.',
        );
    }

    private function verifierAgence(Request $request, Facture $facture): void
    {
        abort_unless($facture->agence_id === $request->user()->agence_id, 404);
    }
}
