<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Mail\InvitationEmployeMail;
use App\Models\Invitation;
use App\Models\ParametreAgence;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipeController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateur = $request->user();

        // Un transitaire solo consulte la page, la gestion reste réservée aux agences.
        abort_unless(
            $utilisateur->permission('gerer_equipe') || $utilisateur->est_transitaire_solo,
            403,
            "Accès réservé aux administrateurs de l'agence."
        );

        $agenceId = $utilisateur->agence_id;

        return view('equipe.index', [
            'peutGerer' => $utilisateur->permission('gerer_equipe'),
            'membres' => User::where('agence_id', $agenceId)
                ->whereIn('role', RoleUtilisateur::valeursInternes())
                ->orderBy('nom')
                ->get(),
            'invitations' => Invitation::where('agence_id', $agenceId)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->autoriser($request);

        return view('equipe.inviter', [
            'roles' => [
                RoleUtilisateur::Transitaire,
                RoleUtilisateur::Comptable,
                RoleUtilisateur::Administrateur,
            ],
            'permissions' => User::PERMISSIONS,
            'descriptions' => User::DESCRIPTIONS_PERMISSIONS,
            'presets' => collect(RoleUtilisateur::internes())
                ->mapWithKeys(fn (RoleUtilisateur $role) => [$role->value => User::permissionsParRole($role)])
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autoriser($request);

        $utilisateur = $request->user();

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'poste' => ['nullable', 'string', 'max:80'],
            'role' => ['required', Rule::in(RoleUtilisateur::valeursInternes())],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::PERMISSIONS))],
        ], [
            'nom.required' => 'Renseignez le nom complet du collaborateur.',
            'email.required' => 'Renseignez son adresse e-mail professionnelle.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'email.unique' => 'Un compte utilise déjà cette adresse e-mail.',
            'role.required' => 'Choisissez un rôle.',
        ]);

        $role = RoleUtilisateur::from($donnees['role']);
        $cochees = $request->input('permissions', []);
        $permissions = collect(User::PERMISSIONS)
            ->map(fn ($libelle, $cle) => in_array($cle, $cochees, true))
            ->all();

        $membre = User::create([
            'agence_id' => $utilisateur->agence_id,
            'nom' => $donnees['nom'],
            'email' => mb_strtolower($donnees['email']),
            'poste' => $donnees['poste'] ?? null,
            'role' => $role,
            'statut_activation' => StatutActivation::EnAttente,
            'password' => null,
            'permissions' => $permissions,
        ]);

        $joursInvitation = max(1, (int) ParametreAgence::valeur($utilisateur->agence_id, 'securite_expiration_invitation', 7));

        $invitation = Invitation::create([
            'agence_id' => $utilisateur->agence_id,
            'user_id' => $membre->id,
            'email' => $membre->email,
            'nom' => $membre->nom,
            'role' => $role,
            'token' => Invitation::genererToken(),
            'expires_at' => now()->addDays($joursInvitation),
            'invited_by' => $utilisateur->id,
        ]);

        $this->envoyer($invitation);

        return redirect()
            ->route('equipe.index')
            ->with('succes', 'Invitation envoyée à '.$membre->email.' (valable 7 jours).')
            ->with('lien_invitation', route('activation.show', $invitation->token));
    }

    public function renvoyer(Request $request, Invitation $invitation): RedirectResponse
    {
        $this->autoriser($request);
        $this->autoriserInvitation($request, $invitation);

        abort_if($invitation->accepted_at !== null, 404);

        $joursInvitation = max(1, (int) ParametreAgence::valeur($invitation->agence_id, 'securite_expiration_invitation', 7));

        $invitation->forceFill([
            'token' => Invitation::genererToken(),
            'expires_at' => now()->addDays($joursInvitation),
        ])->save();

        $this->envoyer($invitation);

        return redirect()
            ->route('equipe.index')
            ->with('succes', 'Invitation renvoyée à '.$invitation->email.' (valable 7 jours).')
            ->with('lien_invitation', route('activation.show', $invitation->token));
    }

    public function annuler(Request $request, Invitation $invitation): RedirectResponse
    {
        $this->autoriser($request);
        $this->autoriserInvitation($request, $invitation);

        $membre = $invitation->user;

        $invitation->delete();

        if ($membre && $membre->statut_activation === StatutActivation::EnAttente) {
            $membre->delete();
        }

        return redirect()->route('equipe.index')->with('succes', 'Invitation annulée.');
    }

    public function changerStatut(Request $request, User $membre): RedirectResponse
    {
        $this->autoriser($request);
        $this->autoriserMembre($request, $membre);

        abort_if($membre->id === $request->user()->id, 403, 'Vous ne pouvez pas désactiver votre propre compte.');

        $membre->forceFill([
            'statut_activation' => $membre->estActif() ? StatutActivation::Expire : StatutActivation::Actif,
        ])->save();

        return redirect()->route('equipe.index')->with(
            'succes',
            $membre->estActif()
                ? 'Le compte de '.$membre->nom.' est de nouveau actif.'
                : 'Le compte de '.$membre->nom.' a été désactivé.'
        );
    }

    public function changerRole(Request $request, User $membre): RedirectResponse
    {
        $this->autoriser($request);
        $this->autoriserMembre($request, $membre);

        abort_if($membre->id === $request->user()->id, 403, 'Vous ne pouvez pas modifier votre propre rôle.');

        $donnees = $request->validate([
            'role' => ['required', Rule::in(RoleUtilisateur::valeursInternes())],
        ], [
            'role.required' => 'Choisissez un rôle.',
        ]);

        $membre->forceFill([
            'role' => RoleUtilisateur::from($donnees['role']),
            'permissions' => null,
        ])->save();

        return redirect()->route('equipe.index')->with('succes', 'Rôle de '.$membre->nom.' mis à jour.');
    }

    private function envoyer(Invitation $invitation): void
    {
        Mail::to($invitation->email)->send(new InvitationEmployeMail($invitation));
    }

    private function autoriser(Request $request): void
    {
        abort_unless(
            $request->user()->permission('gerer_equipe'),
            403,
            "La gestion de l'équipe est réservée aux agences."
        );
    }

    private function autoriserMembre(Request $request, User $membre): void
    {
        abort_unless($membre->agence_id === $request->user()->agence_id, 404);
        abort_if($membre->estClient(), 404);
    }

    private function autoriserInvitation(Request $request, Invitation $invitation): void
    {
        abort_unless($invitation->agence_id === $request->user()->agence_id, 404);
    }
}
