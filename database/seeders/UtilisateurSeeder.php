<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Models\Agence;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $agence = Agence::firstOrFail();

        $comptes = [
            [
                'nom' => 'Kossi Adjovi',
                'email' => 'admin@jalon.tg',
                'role' => RoleUtilisateur::Administrateur,
                'poste' => 'Administrateur d\'agence',
                'telephone' => '+228 90 11 22 33',
            ],
            [
                'nom' => 'Afi Mensah',
                'email' => 'agent@jalon.tg',
                'role' => RoleUtilisateur::Transitaire,
                'poste' => 'Responsable Transit',
                'telephone' => '+228 90 44 55 66',
            ],
            [
                'nom' => 'Yao Kossi',
                'email' => 'comptable@jalon.tg',
                'role' => RoleUtilisateur::Comptable,
                'poste' => 'Comptable',
                'telephone' => '+228 90 77 88 99',
            ],
        ];

        foreach ($comptes as $compte) {
            User::updateOrCreate(
                ['email' => $compte['email']],
                [
                    'agence_id' => $agence->id,
                    'nom' => $compte['nom'],
                    'role' => $compte['role'],
                    'poste' => $compte['poste'],
                    'telephone' => $compte['telephone'],
                    'password' => Hash::make('password'),
                    'statut_activation' => StatutActivation::Actif,
                    'est_transitaire_solo' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
