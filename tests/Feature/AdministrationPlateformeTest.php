<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Tests\TestCase;

class AdministrationPlateformeTest extends TestCase
{
    public function test_guest_is_redirected_from_platform_administration(): void
    {
        $this->get('/plateforme')->assertRedirect('/connexion');
    }

    public function test_agency_administrator_cannot_open_platform_administration(): void
    {
        $utilisateur = new User;
        $utilisateur->forceFill([
            'role' => RoleUtilisateur::Administrateur,
            'est_super_admin' => false,
        ]);

        $this->actingAs($utilisateur)
            ->get('/plateforme')
            ->assertForbidden();
    }

    public function test_super_admin_is_sent_to_the_platform_console(): void
    {
        $utilisateur = new User;
        $utilisateur->forceFill([
            'role' => RoleUtilisateur::Administrateur,
            'est_super_admin' => true,
        ]);

        $this->actingAs($utilisateur)
            ->get('/')
            ->assertRedirect('/plateforme');
    }

    public function test_super_admin_can_open_the_platform_console(): void
    {
        $utilisateur = new User;
        $utilisateur->forceFill([
            'id' => 999999,
            'nom' => 'Administration plateforme',
            'role' => RoleUtilisateur::Administrateur,
            'est_super_admin' => true,
        ]);

        $this->actingAs($utilisateur)
            ->get('/plateforme')
            ->assertOk()
            ->assertSee('Demandes d’agence')
            ->assertSee('Toutes les agences');
    }
}
