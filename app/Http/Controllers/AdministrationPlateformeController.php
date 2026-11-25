<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Mail\InvitationEmployeMail;
use App\Models\Agence;
use App\Models\DemandeAgence;
use App\Models\DemandeDemo;
use App\Models\Invitation;
use App\Models\JournalPlateforme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdministrationPlateformeController extends Controller
{
    public function index(Request $request): View
    {
        $onglet = $request->query('onglet', 'attente');
        $recherche = trim((string) $request->query('recherche', ''));
        $type = (string) $request->query('type', 'toutes');
        $statut = (string) $request->query('statut', 'toutes');

        if ($onglet === 'traitees') {
            $demandes = DemandeAgence::where('statut', '!=', 'en_attente')
                ->with('administrateur')
                ->orderBy('traitee_at', 'desc')
                ->paginate(10, ['*'], 'traitees_page')
                ->withQueryString();
        } else {
            $demandes = DemandeAgence::where('statut', 'en_attente')
                ->latest()
                ->paginate(8, ['*'], 'demandes_page')
                ->withQueryString();
        }

        $agencesQuery = Agence::withCount('users')
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('nom', 'like', '%'.$recherche.'%')
                        ->orWhere('email', 'like', '%'.$recherche.'%');
                });
            })
            ->when($type !== 'toutes', fn ($query) => $query->where('type', $type))
            ->when($statut !== 'toutes', fn ($query) => $query->where('active', $statut === 'actives'))
            ->orderBy('nom');

        return view('plateforme.index', [
            'agencesActives' => Agence::where('active', true)->count(),
            'agencesSuspendues' => Agence::where('active', false)->count(),
            'demandesEnAttente' => DemandeAgence::where('statut', 'en_attente')->count(),
            'demandes' => $demandes,
            'onglet' => $onglet,
            'recherche' => $recherche,
            'type' => $type,
            'statut' => $statut,
            'solos' => Agence::where('type', 'solo')->count(),
            'agencesType' => Agence::where('type', 'agence')->count(),
            'demandesCeMois' => DemandeAgence::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'agences' => $agencesQuery->paginate(10, ['*'], 'agences_page')
                ->withQueryString(),
            'demandesDemo' => DemandeDemo::count(),
            'journalCount' => JournalPlateforme::count(),
            'demos' => DemandeDemo::orderBy('creneau_at')
                ->paginate(10, ['*'], 'demos_page')
                ->withQueryString(),
        ]);
    }

    public function approuver(Request $request, DemandeAgence $demande): RedirectResponse
    {
        abort_unless($demande->statut === 'en_attente', 409);

        if (User::where('email', $demande->email)->exists()) {
            return back()->withErrors([
                'demande' => 'Cette adresse e-mail est déjà rattachée à un compte. Refusez la demande ou contactez le demandeur.',
            ]);
        }

        [$agence, $invitation] = DB::transaction(function () use ($demande, $request) {
            $slugBase = Str::slug($demande->nom_agence) ?: 'agence';
            $slug = $slugBase;
            $suffixe = 2;

            while (Agence::where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$suffixe;
                $suffixe++;
            }

            $agence = Agence::create([
                'nom' => $demande->nom_agence,
                'slug' => $slug,
                'email' => $demande->email,
                'telephone' => $demande->telephone,
                'numero_agrement' => $demande->numero_agrement,
                'type' => 'agence',
                'devise' => 'FCFA',
                'active' => true,
            ]);

            $administrateur = User::create([
                'agence_id' => $agence->id,
                'nom' => $demande->nom_contact,
                'email' => $demande->email,
                'telephone' => $demande->telephone,
                'poste' => 'Administrateur d’agence',
                'password' => Str::random(64),
                'role' => RoleUtilisateur::Administrateur,
                'statut_activation' => StatutActivation::EnAttente,
            ]);

            $invitation = Invitation::create([
                'agence_id' => $agence->id,
                'user_id' => $administrateur->id,
                'email' => $administrateur->email,
                'nom' => $administrateur->nom,
                'role' => RoleUtilisateur::Administrateur,
                'token' => Invitation::genererToken(),
                'expires_at' => now()->addDays(7),
                'invited_by' => $request->user()->id,
            ]);

            $demande->update([
                'statut' => 'approuvee',
                'traitee_par' => $request->user()->id,
                'agence_id' => $agence->id,
                'traitee_at' => now(),
            ]);

            JournalPlateforme::enregistrer('demande_approuvee', $demande);

            return [$agence, $invitation];
        });

        try {
            Mail::to($invitation->email)->send(new InvitationEmployeMail($invitation));
        } catch (\Throwable $exception) {
            Log::error('Échec de l’envoi de l’invitation d’agence', [
                'agence_id' => $agence->id,
                'demande_id' => $demande->id,
                'erreur' => $exception->getMessage(),
            ]);

            return back()->with('avertissement', 'Agence créée, mais l’e-mail n’a pas pu être envoyé. Le lien d’activation est affiché ci-dessous.')
                ->with('lien_activation', $invitation->lien);
        }

        return back()->with('succes', 'Agence « '.$agence->nom.' » créée et invitation envoyée à '.$invitation->email.'.');
    }

    public function refuser(Request $request, DemandeAgence $demande): RedirectResponse
    {
        abort_unless($demande->statut === 'en_attente', 409);

        $donnees = $request->validate([
            'motif_refus' => ['nullable', 'string', 'max:500'],
        ]);

        $demande->update([
            'statut' => 'refusee',
            'motif_refus' => $donnees['motif_refus'] ?: null,
            'traitee_par' => $request->user()->id,
            'traitee_at' => now(),
        ]);

        JournalPlateforme::enregistrer('demande_refusee', $demande, $donnees['motif_refus'] ?? null);

        return back()->with('succes', 'La demande de '.$demande->nom_agence.' a été refusée.');
    }

    public function changerStatutAgence(Request $request, Agence $agence): RedirectResponse
    {
        $suspension = $agence->active;

        if ($suspension && $request->user()->agence_id === $agence->id) {
            return back()->withErrors([
                'agence' => 'Vous ne pouvez pas suspendre votre propre agence.',
            ]);
        }

        $donnees = $request->validate([
            'motif' => $suspension
                ? ['required', 'string', 'min:3', 'max:300']
                : ['nullable', 'string', 'max:300'],
        ], [
            'motif.required' => 'Le motif de suspension est obligatoire.',
            'motif.min' => 'Le motif doit contenir au moins 3 caractères.',
            'motif.max' => 'Le motif ne peut pas dépasser 300 caractères.',
        ]);

        DB::transaction(function () use ($agence, $donnees, $suspension, $request) {
            $agence->update($suspension
                ? [
                    'active' => false,
                    'motif_suspension' => $donnees['motif'],
                    'suspendue_at' => now(),
                ]
                : [
                    'active' => true,
                    'motif_suspension' => null,
                    'suspendue_at' => null,
                ]);

            JournalPlateforme::enregistrer(
                $suspension ? 'agence_suspendue' : 'agence_reactivee',
                $agence,
                $donnees['motif'] ?? null
            );
        });

        return back()->with('succes', $suspension
            ? 'L’agence « '.$agence->nom.' » est suspendue.'
            : 'L’agence « '.$agence->nom.' » est réactivée.');
    }

    public function enregistrerNotes(Request $request, DemandeAgence $demande): RedirectResponse
    {
        $donnees = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'dernier_contact_at' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'notes.max' => 'Les notes ne peuvent pas dépasser 2000 caractères.',
            'dernier_contact_at.date' => 'La date de dernier contact n’est pas valide.',
            'dernier_contact_at.before_or_equal' => 'La date de dernier contact ne peut pas être dans le futur.',
        ]);

        $demande->update([
            'notes' => $donnees['notes'] ?? null,
            'dernier_contact_at' => $donnees['dernier_contact_at'] ?? null,
        ]);

        JournalPlateforme::enregistrer('notes_modifiees', $demande);

        return back()->with('succes', 'Notes et contact mis à jour pour '.$demande->nom_agence.'.');
    }

    public function journal(): View
    {
        return view('plateforme.journal', [
            'entrees' => JournalPlateforme::with('user')
                ->latest()
                ->paginate(20),
        ]);
    }
}
