<?php

namespace App\Http\Controllers;

use App\Enums\StatutConteneur;
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
use App\Services\JournalService;
use App\Services\ReferenceService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DossierController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateur = $request->user();

        $recherche = trim((string) $request->query('q'));
        $etat = (string) $request->query('etat', 'tous');
        $periode = (string) $request->query('periode', '30');
        $responsable = (string) $request->query('responsable', 'tous');

        $requete = Dossier::query()
            ->visiblePour($utilisateur)
            ->with(['client', 'transitaire', 'conteneurs', 'factures'])
            ->withCount('factures');

        if ($recherche !== '') {
            $requete->where(function ($sous) use ($recherche) {
                $motif = '%'.$recherche.'%';

                $sous->where('reference', 'like', $motif)
                    ->orWhere('code_suivi', 'like', $motif)
                    ->orWhere('numero_bl', 'like', $motif)
                    ->orWhereHas('client', fn ($client) => $client->where('nom', 'like', $motif))
                    ->orWhereHas('conteneurs', fn ($conteneur) => $conteneur->where('numero', 'like', $motif));
            });
        }

        if ($periode !== 'tous') {
            $jours = match ((int) $periode) {
                7 => 7,
                90 => 90,
                365 => 365,
                default => 30,
            };

            $requete->where('created_at', '>=', now()->subDays($jours)->startOfDay());
        }

        if ($responsable !== 'tous' && $responsable !== '') {
            $requete->where('transitaire_id', (int) $responsable);
        }

        $enRetard = fn ($sous) => $sous->where('statut', StatutConteneur::AuPort)
            ->whereDate('fin_franchise', '<=', today());

        match ($etat) {
            'brouillon' => $requete->where('brouillon', true),
            'livre' => $requete->where('brouillon', false)->where('jalon_actuel', '>=', 9),
            'en_retard' => $requete->where('brouillon', false)
                ->where('jalon_actuel', '<', 9)
                ->whereHas('conteneurs', $enRetard),
            'en_cours' => $requete->where('brouillon', false)
                ->where('jalon_actuel', '<', 9)
                ->whereDoesntHave('conteneurs', $enRetard),
            default => null,
        };

        $dossiers = $requete->orderByDesc('updated_at')->paginate(7)->withQueryString();

        $total = Dossier::visiblePour($utilisateur)->count();

        $responsables = User::where('agence_id', $utilisateur->agence_id)
            ->whereIn('role', ['administrateur', 'transitaire', 'comptable'])
            ->orderBy('nom')
            ->get();

        return view('dossiers.index', compact(
            'dossiers', 'total', 'responsables', 'recherche', 'etat', 'periode', 'responsable'
        ));
    }

    public function create(Request $request): View
    {
        $this->autoriser($request, 'creer_dossiers');

        $agence = $request->user()->agence;
        $regimes = Regime::with('documentsRequis')->where('actif', true)->orderBy('ordre')->get();

        return view('dossiers.formulaire', [
            'dossier' => null,
            'clients' => Client::where('agence_id', $agence->id)->orderBy('nom')->get(),
            'regimes' => $regimes,
            'documentsParRegime' => $regimes->mapWithKeys(fn (Regime $regime) => [
                $regime->id => $regime->documentsRequis->pluck('libelle')->all(),
            ]),
            'reference' => (new ReferenceService)->apercu($agence, 'dossier'),
            'codeSuivi' => (new ReferenceService)->codeSuivi(),
            'clientSelectionne' => Client::where('agence_id', $agence->id)
                ->where('code', $request->query('client'))
                ->value('id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autoriser($request, 'creer_dossiers');

        $donnees = $this->valider($request);

        $agence = $request->user()->agence;

        $dossier = Dossier::create([
            'agence_id' => $agence->id,
            'client_id' => $donnees['client_id'],
            'transitaire_id' => $request->user()->id,
            'regime_id' => $donnees['regime_id'],
            'reference' => (new ReferenceService)->dossier($agence),
            'code_suivi' => (new ReferenceService)->codeSuivi(),
            'ligne_maritime' => $donnees['ligne_maritime'],
            'navire' => $donnees['navire'] ?? null,
            'numero_bl' => $donnees['numero_bl'] ?? null,
            'nature_marchandise' => $donnees['nature_marchandise'],
            'pays_origine' => $donnees['pays_origine'] ?? null,
            'date_arrivee_prevue' => $donnees['date_arrivee_prevue'] ?? null,
            'jalon_actuel' => 1,
            'brouillon' => $request->boolean('brouillon'),
            'marge_previsionnelle' => 0,
            'date_ouverture' => today(),
        ]);

        HistoriqueStatut::create([
            'dossier_id' => $dossier->id,
            'user_id' => $request->user()->id,
            'jalon' => 1,
            'jalon_precedent' => null,
            'action' => 'creation',
            'commentaire' => 'Création du dossier et enregistrement du client.',
            'date_passage' => today(),
        ]);

        app(JournalService::class)->enregistrer(
            'dossier',
            'Dossier '.$dossier->reference.' créé pour '.$dossier->client->nom.($dossier->brouillon ? ' (brouillon)' : ''),
            $dossier
        );

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', $dossier->brouillon
                ? 'Brouillon enregistré : '.$dossier->reference
                : 'Dossier '.$dossier->reference.' créé.');
    }

    public function show(Request $request, Dossier $dossier): View
    {
        $this->autoriserConsultation($request, $dossier);

        $onglet = (string) $request->query('onglet', 'apercu');

        if (! in_array($onglet, ['apercu', 'documents', 'conteneurs', 'couts', 'facture', 'historique', 'alertes'], true)) {
            $onglet = 'apercu';
        }

        $dossier->load([
            'client', 'transitaire', 'regime.documentsRequis',
            'conteneurs.evenements', 'documents.validateur', 'documents.auteur',
            'couts.auteur', 'factures.lignes', 'factures.paiements',
            'historique.auteur', 'alertes',
        ]);

        $documentsRequis = $dossier->regime->documentsRequis;
        $documentsValides = $dossier->documents->where('valide', true)->count();

        return view('dossiers.show', [
            'dossier' => $dossier,
            'onglet' => $onglet,
            'documentsRequis' => $documentsRequis,
            'documentsValides' => $documentsValides,
            'jalons' => JalonConfig::tous(),
            'jalonSuivant' => $dossier->jalon_suivant,
        ]);
    }

    public function edit(Request $request, Dossier $dossier): View
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'modifier_dossiers');

        $regimes = Regime::with('documentsRequis')->where('actif', true)->orderBy('ordre')->get();

        return view('dossiers.formulaire', [
            'dossier' => $dossier,
            'clients' => Client::where('agence_id', $dossier->agence_id)->orderBy('nom')->get(),
            'regimes' => $regimes,
            'documentsParRegime' => $regimes->mapWithKeys(fn (Regime $regime) => [
                $regime->id => $regime->documentsRequis->pluck('libelle')->all(),
            ]),
            'reference' => $dossier->reference,
            'codeSuivi' => $dossier->code_suivi,
            'clientSelectionne' => null,
        ]);
    }

    public function update(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'modifier_dossiers');

        $donnees = $this->valider($request, $dossier);

        $dossier->update($donnees + [
            'brouillon' => $request->boolean('brouillon'),
        ]);

        app(JournalService::class)->enregistrer('dossier', 'Dossier '.$dossier->reference.' modifié', $dossier);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Le dossier a été mis à jour.');
    }

    public function destroy(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'modifier_dossiers');

        $reference = $dossier->reference;
        $dossier->delete();

        app(JournalService::class)->enregistrer('dossier', 'Dossier '.$reference.' archivé');

        return redirect()->route('dossiers.index')->with('succes', 'Le dossier '.$reference.' a été archivé.');
    }

    public function avancerJalon(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'modifier_dossiers');

        abort_if($dossier->jalon_actuel >= 9, 422, 'Le dossier est déjà au dernier jalon.');

        $donnees = $request->validate([
            'date_passage' => ['required', 'date', 'before_or_equal:today'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ], [
            'date_passage.required' => 'Renseignez la date de passage.',
            'date_passage.before_or_equal' => 'La date de passage ne peut pas être dans le futur.',
        ]);

        $nouveau = $dossier->jalon_actuel + 1;
        $configuration = JalonConfig::tous()->firstWhere('ordre', $nouveau);

        HistoriqueStatut::create([
            'dossier_id' => $dossier->id,
            'user_id' => $request->user()->id,
            'jalon' => $nouveau,
            'jalon_precedent' => $dossier->jalon_actuel,
            'action' => 'avance',
            'commentaire' => $donnees['commentaire'] ?? null,
            'date_passage' => $donnees['date_passage'],
        ]);

        $dossier->forceFill([
            'jalon_actuel' => $nouveau,
            'date_cloture' => $nouveau === 9 ? $donnees['date_passage'] : null,
        ])->save();

        if ($nouveau === 9) {
            $dossier->conteneurs()
                ->where('statut', StatutConteneur::AuPort)
                ->update(['statut' => StatutConteneur::Sorti, 'date_sortie' => $donnees['date_passage']]);
        }

        app(JournalService::class)->enregistrer(
            'jalon',
            'Dossier '.$dossier->reference.' passé au jalon '.$nouveau.' ('.($configuration?->nom ?? 'jalon '.$nouveau).')',
            $dossier
        );

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Jalon '.$nouveau.' enregistré : '.($configuration?->nom ?? 'jalon '.$nouveau).'.');
    }

    public function reculerJalon(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);

        abort_unless($request->user()->peutReculerJalon(), 403, 'Seul un administrateur peut reculer un jalon.');
        abort_if($dossier->jalon_actuel <= 1, 422, 'Le dossier est déjà au premier jalon.');

        $donnees = $request->validate([
            'motif' => ['required', 'string', 'max:300'],
        ], [
            'motif.required' => 'Indiquez le motif du recul.',
        ]);

        $precedent = $dossier->jalon_actuel - 1;

        HistoriqueStatut::create([
            'dossier_id' => $dossier->id,
            'user_id' => $request->user()->id,
            'jalon' => $precedent,
            'jalon_precedent' => $dossier->jalon_actuel,
            'action' => 'recul',
            'commentaire' => $donnees['motif'],
            'date_passage' => today(),
        ]);

        $dossier->forceFill([
            'jalon_actuel' => $precedent,
            'date_cloture' => null,
        ])->save();

        app(JournalService::class)->enregistrer(
            'jalon',
            'Dossier '.$dossier->reference.' reculé au jalon '.$precedent.' ('.$donnees['motif'].')',
            $dossier
        );

        return redirect()->route('dossiers.show', $dossier)->with('succes', 'Le dossier est revenu au jalon '.$precedent.'.');
    }

    public function ajouterDocument(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'ajouter_documents');

        $donnees = $request->validate([
            'type' => ['required', 'string', 'max:120'],
            'fichier' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'visible_client' => ['nullable', 'boolean'],
        ], [
            'type.required' => 'Précisez le type de document.',
            'fichier.required' => 'Choisissez un fichier.',
            'fichier.mimes' => 'Formats acceptés : PDF, JPG ou PNG.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $fichier = $request->file('fichier');
        $chemin = $fichier->store('documents/'.$dossier->id, 'local');

        Document::create([
            'dossier_id' => $dossier->id,
            'type' => $donnees['type'],
            'nom_original' => $fichier->getClientOriginalName(),
            'chemin' => $chemin,
            'mime' => $fichier->getClientMimeType(),
            'taille' => $fichier->getSize(),
            'valide' => false,
            'visible_client' => $request->boolean('visible_client'),
            'ajoute_par' => $request->user()->id,
        ]);

        app(JournalService::class)->enregistrer(
            'document',
            'Document « '.$donnees['type'].' » ajouté au dossier '.$dossier->reference,
            $dossier
        );

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'documents'])
            ->with('succes', 'Document ajouté. Il reste à le valider.');
    }

    public function validerDocument(Request $request, Dossier $dossier, Document $document): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'ajouter_documents');
        abort_unless($document->dossier_id === $dossier->id, 404);

        $document->forceFill([
            'valide' => ! $document->valide,
            'valide_par' => $document->valide ? null : $request->user()->id,
            'date_validation' => $document->valide ? null : now(),
        ])->save();

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'documents'])
            ->with('succes', $document->valide ? 'Document validé.' : 'Validation retirée.');
    }

    public function supprimerDocument(Request $request, Dossier $dossier, Document $document): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'ajouter_documents');
        abort_unless($document->dossier_id === $dossier->id, 404);

        Storage::disk('local')->delete($document->chemin);
        $document->delete();

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'documents'])
            ->with('succes', 'Document supprimé.');
    }

    public function telechargerDocument(Request $request, Dossier $dossier, Document $document)
    {
        $this->autoriserConsultation($request, $dossier);
        abort_unless($document->dossier_id === $dossier->id, 404);
        abort_unless(Storage::disk('local')->exists($document->chemin), 404, 'Fichier introuvable.');

        return Storage::disk('local')->download($document->chemin, $document->nom_original);
    }

    public function ajouterConteneur(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'modifier_dossiers');

        $donnees = $request->validate([
            'numero' => ['required', 'string', 'max:20', 'unique:conteneurs,numero'],
            'type' => ['required', Rule::in(['20DV', '40DV', '20HC', '40HC', '20RF', '40RF'])],
            'date_arrivee' => ['nullable', 'date'],
            'numero_plomb' => ['nullable', 'string', 'max:30'],
        ], [
            'numero.required' => 'Renseignez le numéro du conteneur.',
            'numero.unique' => 'Ce numéro de conteneur est déjà enregistré.',
            'type.required' => 'Choisissez un type de conteneur.',
        ]);

        $conteneur = Conteneur::create([
            'dossier_id' => $dossier->id,
            'numero' => mb_strtoupper(str_replace(' ', '', $donnees['numero'])),
            'type' => $donnees['type'],
            'taille' => str_starts_with($donnees['type'], '20') ? '20 pieds' : '40 pieds',
            'statut' => StatutConteneur::AuPort,
            'date_arrivee' => $donnees['date_arrivee'] ?? today(),
            'ligne_maritime' => $dossier->ligne_maritime,
            'numero_plomb' => $donnees['numero_plomb'] ?? null,
        ]);

        app(FranchiseService::class)->appliquer($conteneur, $dossier->ligne_maritime);

        $conteneur->evenements()->create([
            'type' => 'arrivee',
            'libelle' => 'Arrivée au port de Lomé',
            'date_evenement' => $conteneur->date_arrivee ?? today(),
            'description' => 'Conteneur enregistré sur le dossier '.$dossier->reference.'.',
        ]);

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'conteneurs'])
            ->with('succes', 'Conteneur '.$conteneur->numero_formate.' ajouté.');
    }

    public function ajouterCout(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'saisir_couts');

        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:120'],
            'categorie' => ['required', Rule::in(['transit', 'douane', 'manutention', 'transport', 'autre'])],
            'montant' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'date' => ['required', 'date'],
            'fournisseur' => ['nullable', 'string', 'max:120'],
        ], [
            'libelle.required' => 'Renseignez le libellé du coût.',
            'montant.required' => 'Renseignez le montant.',
            'montant.numeric' => 'Le montant doit être un nombre.',
        ]);

        Cout::create($donnees + [
            'dossier_id' => $dossier->id,
            'saisi_par' => $request->user()->id,
        ]);

        app(JournalService::class)->enregistrer(
            'cout',
            'Coût de '.Format::fcfa($donnees['montant']).' ajouté au dossier '.$dossier->reference,
            $dossier
        );

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'couts'])
            ->with('succes', 'Coût enregistré.');
    }

    public function supprimerCout(Request $request, Dossier $dossier, Cout $cout): RedirectResponse
    {
        $this->autoriserConsultation($request, $dossier);
        $this->autoriser($request, 'saisir_couts');
        abort_unless($cout->dossier_id === $dossier->id, 404);

        $cout->delete();

        return redirect()
            ->route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'couts'])
            ->with('succes', 'Coût supprimé.');
    }

    /** @return array<string, mixed> */
    private function valider(Request $request, ?Dossier $dossier = null): array
    {
        return $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('agence_id', $request->user()->agence_id)],
            'regime_id' => ['required', Rule::exists('regimes', 'id')],
            'ligne_maritime' => ['required', 'string', 'max:80'],
            'navire' => ['nullable', 'string', 'max:120'],
            'numero_bl' => ['nullable', 'string', 'max:80'],
            'nature_marchandise' => ['required', 'string', 'max:190'],
            'pays_origine' => ['nullable', 'string', 'max:80'],
            'date_arrivee_prevue' => ['nullable', 'date'],
        ], [
            'client_id.required' => 'Choisissez le client.',
            'regime_id.required' => 'Choisissez le régime douanier.',
            'ligne_maritime.required' => 'Indiquez la ligne maritime.',
            'nature_marchandise.required' => 'Précisez la nature de la marchandise.',
        ]);
    }

    private function autoriser(Request $request, string $permission): void
    {
        abort_unless($request->user()->permission($permission) || $request->user()->estAdministrateur(), 403, 'Action non autorisée pour votre compte.');
    }

    private function autoriserConsultation(Request $request, Dossier $dossier): void
    {
        abort_unless($dossier->estVisiblePar($request->user()), 404);
    }
}
