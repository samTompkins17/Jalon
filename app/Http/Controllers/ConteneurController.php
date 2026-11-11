<?php

namespace App\Http\Controllers;

use App\Enums\StatutConteneur;
use App\Models\Conteneur;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConteneurController extends Controller
{
    /**
     * Liste des conteneurs de l'agence, filtrable par statut et par recherche.
     */
    public function index(Request $request): View
    {
        $this->autoriser($request);

        $recherche = trim((string) $request->query('q'));
        $statut = (string) $request->query('statut', 'tous');
        $agenceId = $request->user()->agence_id;

        $base = Conteneur::query()
            ->whereHas('dossier', fn ($dossier) => $dossier->where('agence_id', $agenceId));

        $compteurs = [
            'tous' => (clone $base)->count(),
            'au_port' => (clone $base)->where('statut', StatutConteneur::AuPort)->count(),
            'sorti' => (clone $base)->where('statut', StatutConteneur::Sorti)->count(),
            'restitue' => (clone $base)->where('statut', StatutConteneur::Restitue)->count(),
        ];

        $requete = (clone $base)->with(['dossier.client']);

        if ($recherche !== '') {
            $motif = '%'.$recherche.'%';

            $requete->where(function ($sous) use ($motif) {
                $sous->where('numero', 'like', $motif)
                    ->orWhereHas('dossier', fn ($dossier) => $dossier
                        ->where('reference', 'like', $motif)
                        ->orWhereHas('client', fn ($client) => $client->where('nom', 'like', $motif)));
            });
        }

        if (in_array($statut, ['au_port', 'sorti', 'restitue'], true)) {
            $requete->where('statut', $statut);
        } else {
            $statut = 'tous';
        }

        $conteneurs = $requete
            ->orderByRaw("CASE statut WHEN 'au_port' THEN 0 WHEN 'sorti' THEN 1 ELSE 2 END")
            ->orderBy('fin_franchise')
            ->orderBy('numero')
            ->paginate(10)
            ->withQueryString();

        return view('conteneurs.index', [
            'conteneurs' => $conteneurs,
            'compteurs' => $compteurs,
            'recherche' => $recherche,
            'statut' => $statut,
        ]);
    }

    /**
     * Fiche d'un conteneur : informations techniques et traçabilité portuaire.
     */
    public function show(Request $request, Conteneur $conteneur): View
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $conteneur);

        $conteneur->load(['dossier.client', 'evenements.auteur']);

        return view('conteneurs.show', [
            'conteneur' => $conteneur,
        ]);
    }

    /**
     * Mise à jour manuelle du statut du conteneur, tracée dans l'historique.
     */
    public function updateStatut(Request $request, Conteneur $conteneur, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $conteneur);

        $donnees = $request->validate([
            'statut' => ['required', Rule::enum(StatutConteneur::class)],
        ], [
            'statut.required' => 'Choisissez un statut.',
        ]);

        $nouveau = StatutConteneur::from($donnees['statut']);

        if ($nouveau === $conteneur->statut) {
            return back()->with('statut', 'Le conteneur est déjà au statut « '.$nouveau->label().' ».');
        }

        $conteneur->statut = $nouveau;

        // Un retour en arrière efface les dates qui ne correspondent plus au
        // statut, sinon la fiche affiche une sortie ou une restitution que le
        // statut courant contredit.
        if ($nouveau === StatutConteneur::AuPort) {
            $conteneur->date_sortie = null;
            $conteneur->date_restitution = null;
        } elseif ($nouveau === StatutConteneur::Sorti) {
            $conteneur->date_sortie ??= today();
            $conteneur->date_restitution = null;
        } else {
            if ($conteneur->date_sortie === null) {
                $conteneur->date_sortie = today();
            }
            $conteneur->date_restitution ??= today();
        }

        $conteneur->save();

        $libelle = match ($nouveau) {
            StatutConteneur::AuPort => 'Conteneur de nouveau au port',
            StatutConteneur::Sorti => 'Sortie du port enregistrée',
            StatutConteneur::Restitue => 'Conteneur restitué',
        };

        $conteneur->evenements()->create([
            'type' => 'statut',
            'libelle' => $libelle,
            'description' => 'Statut mis à jour depuis la fiche conteneur.',
            'date_evenement' => today(),
            'user_id' => $request->user()->id,
        ]);

        $journal->enregistrer(
            'Statut conteneur',
            $conteneur->numero_formate.' passé au statut « '.$nouveau->label().' ».',
            $conteneur->dossier,
            $request->user(),
            $conteneur,
        );

        return redirect()
            ->route('conteneurs.show', $conteneur)
            ->with('statut', 'Le statut du conteneur a été mis à jour.');
    }

    /** Le suivi des conteneurs est réservé à l'agence et à la comptabilité. */
    private function autoriser(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role->value, ['administrateur', 'transitaire'], true),
            403,
        );
    }

    private function verifierAgence(Request $request, Conteneur $conteneur): void
    {
        abort_unless($conteneur->dossier?->agence_id === $request->user()->agence_id, 404);
    }
}
