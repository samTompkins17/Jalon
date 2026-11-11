<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Mail\InvitationEmployeMail;
use App\Models\Client;
use App\Models\Invitation;
use App\Models\User;
use App\Services\JournalService;
use App\Services\ReferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->autoriser($request);

        $recherche = trim((string) $request->query('q'));
        $agenceId = $request->user()->agence_id;

        $requete = Client::query()->deLAgence($agenceId)->withCount('dossiers');

        if ($recherche !== '') {
            $motif = '%'.$recherche.'%';

            $requete->where(function ($sous) use ($motif) {
                $sous->where('nom', 'like', $motif)
                    ->orWhere('code', 'like', $motif)
                    ->orWhere('contact_nom', 'like', $motif)
                    ->orWhere('email', 'like', $motif)
                    ->orWhere('telephone', 'like', $motif);
            });
        }

        $clients = $requete->orderBy('nom')->paginate(6)->withQueryString();

        // Client affiché dans le panneau de droite : celui demandé, sinon le premier.
        $selection = null;

        if ($request->filled('client')) {
            $selection = Client::deLAgence($agenceId)->where('code', $request->query('client'))->first();
        }

        $selection ??= $clients->first();

        if ($selection) {
            $selection->loadCount('dossiers');
            $selection->load(['dossiers' => fn ($dossiers) => $dossiers->latest()->limit(1), 'utilisateur']);
        }

        $retard = $selection
            ? $selection->dossiers()
                ->whereHas('conteneurs', fn ($conteneur) => $conteneur
                    ->where('statut', 'au_port')
                    ->whereDate('fin_franchise', '<=', today()))
                ->count()
            : 0;

        return view('clients.index', [
            'clients' => $clients,
            'selection' => $selection,
            'retard' => $retard,
            'recherche' => $recherche,
        ]);
    }

    public function show(Request $request, Client $client): View
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $client);

        $client->load([
            'dossiers' => fn ($dossiers) => $dossiers->latest('date_ouverture'),
            'factures' => fn ($factures) => $factures->latest('date_facture'),
            'utilisateur',
        ]);

        $totalFacture = (float) $client->factures->sum('total');
        $totalPaye = (float) $client->factures->flatMap->paiements->sum('montant');

        return view('clients.show', [
            'client' => $client,
            'totalFacture' => $totalFacture,
            'soldeDu' => max(0, $totalFacture - $totalPaye),
            'dossiersEnRetard' => $client->dossiers->filter(fn ($dossier) => $dossier->etat === 'en_retard')->count(),
            'invitation' => Invitation::where('client_id', $client->id)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->autoriser($request);

        return view('clients.formulaire', [
            'client' => new Client(['type' => 'entreprise', 'pays' => 'Togo', 'ville' => 'Lomé', 'statut' => 'actif']),
            'mode' => 'creation',
        ]);
    }

    public function store(Request $request, ReferenceService $references, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);

        $donnees = $this->valider($request);

        $client = Client::create([
            ...$donnees,
            'agence_id' => $request->user()->agence_id,
            'transitaire_id' => $request->user()->id,
            'code' => $references->client($request->user()->agence),
            'statut' => 'actif',
        ]);

        $journal->enregistrer('Création client', 'Client '.$client->nom.' ajouté au répertoire.', null, $request->user(), $client);

        if ($request->boolean('inviter') && $client->email) {
            $this->inviter($client, $request->user());

            return redirect()
                ->route('clients.show', $client)
                ->with('succes', 'Client créé. Une invitation à l\'espace client a été envoyée à '.$client->email.'.');
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('succes', 'Client '.$client->code.' créé.');
    }

    public function edit(Request $request, Client $client): View
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $client);

        return view('clients.formulaire', [
            'client' => $client,
            'mode' => 'edition',
        ]);
    }

    public function update(Request $request, Client $client, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $client);

        $client->update($this->valider($request, $client));

        $journal->enregistrer('Modification client', 'Fiche de '.$client->nom.' mise à jour.', null, $request->user(), $client);

        return redirect()
            ->route('clients.show', $client)
            ->with('succes', 'Fiche client mise à jour.');
    }

    public function archiver(Request $request, Client $client, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $client);

        $nom = $client->nom;
        $client->delete();

        $journal->enregistrer('Archivage client', 'Client '.$nom.' archivé.', null, $request->user());

        return redirect()
            ->route('clients.index')
            ->with('succes', 'Le client '.$nom.' a été archivé.');
    }

    /** Renvoie une invitation à l'espace client (lien valable 7 jours). */
    public function renvoyerInvitation(Request $request, Client $client): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $client);

        if (! $client->email) {
            return back()->with('erreur', 'Renseignez d\'abord une adresse e-mail pour ce client.');
        }

        $this->inviter($client, $request->user());

        return back()->with('succes', 'Invitation renvoyée à '.$client->email.' (valable 7 jours).');
    }

    // ------------------------------------------------------------------ Aides

    private function valider(Request $request, ?Client $client = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['entreprise', 'particulier'])],
            'nom' => ['required', 'string', 'max:150'],
            'contact_nom' => ['required', 'string', 'max:120'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:150'],
            'ville' => ['nullable', 'string', 'max:80'],
            'adresse' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'type.required' => 'Choisissez le type de client.',
            'nom.required' => 'Renseignez le nom ou la raison sociale.',
            'contact_nom.required' => 'Renseignez le contact principal.',
            'telephone.required' => 'Renseignez un numéro de téléphone.',
            'email.required' => 'Renseignez une adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
        ]);
    }

    private function inviter(Client $client, User $auteur): void
    {
        $utilisateur = $client->utilisateur ?? User::create([
            'agence_id' => $client->agence_id,
            'nom' => $client->contact_nom ?: $client->nom,
            'email' => mb_strtolower($client->email),
            'poste' => $client->fonction,
            'role' => RoleUtilisateur::Client,
            'client_id' => $client->id,
            'statut_activation' => StatutActivation::EnAttente,
            'password' => null,
        ]);

        // Une invitation encore valable est réutilisée, sinon on en crée une nouvelle.
        $invitation = Invitation::where('client_id', $client->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $invitation) {
            $invitation = Invitation::create([
                'agence_id' => $client->agence_id,
                'user_id' => $utilisateur->id,
                'client_id' => $client->id,
                'email' => mb_strtolower($client->email),
                'nom' => $client->nom,
                'role' => RoleUtilisateur::Client,
                'token' => Invitation::genererToken(),
                'expires_at' => now()->addDays(7),
                'invited_by' => $auteur->id,
            ]);
        }

        Mail::to($invitation->email)->send(new InvitationEmployeMail($invitation));
    }

    private function autoriser(Request $request): void
    {
        abort_unless($request->user()->permission('gerer_clients'), 403, 'Accès réservé aux responsables clients.');
    }

    private function verifierAgence(Request $request, Client $client): void
    {
        abort_unless($client->agence_id === $request->user()->agence_id, 404);
    }
}
