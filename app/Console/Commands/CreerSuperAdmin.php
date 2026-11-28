<?php

namespace App\Console\Commands;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
class CreerSuperAdmin extends Command
{
    protected $signature = 'jalon:creer-super-admin {email} {--nom=}';

    protected $description = 'Crée un compte d’administration plateforme sans agence.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Un compte existe déjà avec cette adresse e-mail.');

            return self::FAILURE;
        }

        $nom = trim((string) ($this->option('nom') ?: $this->ask('Nom complet')));
        if ($nom === '') {
            $this->error('Le nom complet est obligatoire.');

            return self::FAILURE;
        }

        $motDePasse = (string) $this->secret('Mot de passe (12 caractères minimum, lettres et chiffres)');
        $confirmation = (string) $this->secret('Confirmer le mot de passe');

        if (
            strlen($motDePasse) < 12
            || ! preg_match('/[A-Za-z]/', $motDePasse)
            || ! preg_match('/\d/', $motDePasse)
            || ! hash_equals($motDePasse, $confirmation)
        ) {
            $this->error('Mot de passe invalide ou confirmation différente.');

            return self::FAILURE;
        }

        $utilisateur = new User;
        $utilisateur->forceFill([
            'nom' => $nom,
            'email' => $email,
            'password' => Hash::make($motDePasse),
            'role' => RoleUtilisateur::Administrateur,
            'statut_activation' => StatutActivation::Actif,
            'est_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info('Compte super-admin créé : '.$utilisateur->email);

        return self::SUCCESS;
    }
}
