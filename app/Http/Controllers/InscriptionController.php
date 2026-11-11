<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Models\Agence;
use App\Models\DemandeAgence;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.inscription', [
            'type' => $request->query('vous') === 'agence' ? 'agence' : 'solo',
        ]);
    }

    /** Création d'un compte transitaire solo : l'agence est créée en même temps. */
    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'telephone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8'],
            'numero_agrement' => ['nullable', 'string', 'max:80'],
        ], [
            'nom.required' => 'Renseignez votre nom complet.',
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => "Cette adresse e-mail n'est pas valide.",
            'email.unique' => 'Un compte existe déjà avec cette adresse e-mail.',
            'telephone.required' => 'Renseignez votre numéro de téléphone.',
            'password.required' => 'Choisissez un mot de passe.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $utilisateur = DB::transaction(function () use ($donnees) {
            $agence = Agence::create([
                'nom' => 'Cabinet '.$donnees['nom'],
                'slug' => $this->slugDisponible($donnees['nom']),
                'email' => $donnees['email'],
                'telephone' => $donnees['telephone'],
                'ville' => 'Lomé',
                'pays' => 'Togo',
                'numero_agrement' => $donnees['numero_agrement'] ?? null,
                'type' => 'solo',
                'devise' => 'FCFA',
            ]);

            return User::create([
                'agence_id' => $agence->id,
                'nom' => $donnees['nom'],
                'email' => $donnees['email'],
                'telephone' => $donnees['telephone'],
                'password' => $donnees['password'],
                'role' => RoleUtilisateur::Transitaire,
                'statut_activation' => StatutActivation::Actif,
                'est_transitaire_solo' => true,
                'derniere_connexion_at' => now(),
            ]);
        });

        Auth::login($utilisateur);
        $request->session()->regenerate();

        return redirect()
            ->route('tableau-de-bord')
            ->with('succes', 'Votre espace est prêt. Bienvenue sur Jalon !');
    }

    /** Formule agence : la demande est transmise au service d'assistance. */
    public function demanderAgence(Request $request): RedirectResponse
    {
        $request->validate([
            'nom_agence' => ['required', 'string', 'max:120'],
            'nom_contact' => ['required', 'string', 'max:120'],
            'telephone' => ['required', 'string', 'max:40'],
            'email' => [
                'required', 'string', 'email', 'max:150', 'unique:users,email',
                Rule::unique('demandes_agence', 'email')->where('statut', 'en_attente'),
            ],
            'numero_agrement' => ['nullable', 'string', 'max:80'],
        ], [
            'nom_agence.required' => "Renseignez le nom de l'agence.",
            'nom_contact.required' => 'Renseignez le nom du responsable.',
            'telephone.required' => 'Renseignez un numéro de téléphone.',
            'email.required' => 'Renseignez votre adresse e-mail professionnelle.',
            'email.email' => "Cette adresse e-mail n'est pas valide.",
            'email.unique' => 'Une demande en attente existe déjà pour cette adresse e-mail.',
        ]);

        DemandeAgence::create([
            'nom_agence' => $request->string('nom_agence')->trim()->toString(),
            'nom_contact' => $request->string('nom_contact')->trim()->toString(),
            'email' => mb_strtolower($request->string('email')->trim()->toString()),
            'telephone' => $request->string('telephone')->trim()->toString(),
            'numero_agrement' => $request->string('numero_agrement')->trim()->toString() ?: null,
        ]);

        return redirect()
            ->route('inscription', ['vous' => 'agence'])
            ->with('succes', 'Votre demande a été enregistrée. Un administrateur plateforme l’examinera et vous recevrez un e-mail après approbation.');
    }

    private function slugDisponible(string $nom): string
    {
        $base = Str::slug($nom) ?: 'agence';
        $slug = $base;
        $suffixe = 2;

        while (Agence::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffixe;
            $suffixe++;
        }

        return $slug;
    }
}
