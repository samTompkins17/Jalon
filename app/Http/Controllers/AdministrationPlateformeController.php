<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Mail\InvitationEmployeMail;
use App\Models\Agence;
use App\Models\DemandeAgence;
use App\Models\Invitation;
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
    public function index(): View
    {
        return view('plateforme.index', [
            'agencesActives' => Agence::where('active', true)->count(),
            'agencesSuspendues' => Agence::where('active', false)->count(),
            'demandesEnAttente' => DemandeAgence::where('statut', 'en_attente')->count(),
            'demandes' => DemandeAgence::where('statut', 'en_attente')
                ->latest()
                ->paginate(8, ['*'], 'demandes_page'),
            'agences' => Agence::withCount('users')->orderBy('nom')
                ->paginate(10, ['*'], 'agences_page'),
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

        return back()->with('succes', 'La demande de '.$demande->nom_agence.' a été refusée.');
    }

    public function changerStatutAgence(Agence $agence): RedirectResponse
    {
        $agence->update(['active' => ! $agence->active]);

        return back()->with('succes', $agence->active
            ? 'L’agence « '.$agence->nom.' » est réactivée.'
            : 'L’agence « '.$agence->nom.' » est suspendue.');
    }
}
