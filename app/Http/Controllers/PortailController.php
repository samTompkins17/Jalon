<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Dossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Espace client en lecture seule : dossiers, documents partagés et factures
 * du client connecté. Aucune action d'écriture n'y est exposée.
 */
class PortailController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Un utilisateur interne qui ouvre le portail est renvoyé vers son tableau de bord.
        if (! $request->user()->estClient()) {
            return redirect()->route('tableau-de-bord');
        }

        $client = $this->client($request);

        $dossiers = $client->dossiers()
            ->with(['conteneurs', 'regime', 'factures.paiements'])
            ->latest('date_ouverture')
            ->get();

        return view('portail.index', [
            'client' => $client,
            'dossiers' => $dossiers,
        ]);
    }

    public function dossier(Request $request, Dossier $dossier): View
    {
        $client = $this->client($request);

        abort_unless($dossier->client_id === $client->id, 404);

        $dossier->load([
            'regime',
            'conteneurs',
            'factures.paiements',
            'documents' => fn ($documents) => $documents->where('visible_client', true)->latest(),
        ]);

        return view('portail.dossier', [
            'client' => $client,
            'dossier' => $dossier,
        ]);
    }

    /** Téléchargement d'un document partagé avec le client. */
    public function document(Request $request, Dossier $dossier, Document $document): StreamedResponse
    {
        $client = $this->client($request);

        abort_unless($dossier->client_id === $client->id && $document->dossier_id === $dossier->id, 404);
        abort_unless($document->visible_client, 403);
        abort_unless(Storage::disk('local')->exists($document->chemin), 404, 'Le fichier est introuvable.');

        return Storage::disk('local')->download($document->chemin, $document->nom_original);
    }

    /** Suivi public par code (sans authentification, informations limitées). */
    public function suivi(Request $request, ?string $code = null): View
    {
        $code = trim((string) ($code ?? $request->query('code')));
        $dossier = null;
        $erreur = null;

        if ($code !== '') {
            $dossier = Dossier::where('code_suivi', mb_strtoupper($code))
                ->with(['regime', 'conteneurs'])
                ->first();

            if (! $dossier) {
                $erreur = 'Aucun dossier ne correspond à ce code de suivi.';
            }
        }

        return view('portail.suivi', [
            'code' => $code,
            'dossier' => $dossier,
            'erreur' => $erreur,
        ]);
    }

    private function client(Request $request)
    {
        $utilisateur = $request->user();

        abort_unless($utilisateur?->estClient(), 403, 'Le portail est réservé aux comptes clients.');
        abort_unless($utilisateur->client_id, 403, 'Votre compte n\'est rattaché à aucune fiche client.');

        return $utilisateur->client;
    }
}
