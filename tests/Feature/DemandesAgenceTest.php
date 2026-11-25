<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Mail\InvitationEmployeMail;
use App\Models\Agence;
use App\Models\DemandeAgence;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemandesAgenceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_agency_signup_request_is_persisted(): void
    {
        $email = 'demande-'.Str::uuid().'@example.test';

        $this->post(route('inscription.agence'), [
            'nom_agence' => 'Transit Test',
            'nom_contact' => 'Afi Test',
            'email' => $email,
            'telephone' => '+228 90 00 00 01',
            'numero_agrement' => 'AG-TEST-01',
        ])->assertRedirect(route('inscription', ['vous' => 'agence']));

        $this->assertDatabaseHas('demandes_agence', [
            'nom_agence' => 'Transit Test',
            'email' => $email,
            'statut' => 'en_attente',
        ]);
    }

    public function test_approval_creates_agency_admin_invitation_and_sends_email(): void
    {
        Mail::fake();
        $superAdmin = $this->creerSuperAdmin();
        $demande = $this->creerDemande();

        $this->actingAs($superAdmin)
            ->post(route('plateforme.demandes.approuver', $demande))
            ->assertRedirect();

        $this->assertDatabaseHas('demandes_agence', [
            'id' => $demande->id,
            'statut' => 'approuvee',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => $demande->email,
            'role' => RoleUtilisateur::Administrateur->value,
            'statut_activation' => StatutActivation::EnAttente->value,
        ]);
        $this->assertDatabaseHas('invitations', [
            'email' => $demande->email,
            'role' => RoleUtilisateur::Administrateur->value,
            'accepted_at' => null,
        ]);
        Mail::assertSent(InvitationEmployeMail::class, fn (InvitationEmployeMail $mail) => $mail->hasTo($demande->email));
    }

    public function test_platform_admin_can_suspend_an_agency(): void
    {
        $superAdmin = $this->creerSuperAdmin();
        $agence = $this->creerAgence();

        $this->actingAs($superAdmin)
            ->post(route('plateforme.agences.statut', $agence), [
                'motif' => 'Impayés signalés par la comptabilité',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('agences', [
            'id' => $agence->id,
            'active' => false,
            'motif_suspension' => 'Impayés signalés par la comptabilité',
        ]);
        $this->assertNotNull($agence->fresh()->suspendue_at);
    }

    public function test_suspension_sans_motif_echoue_validation(): void
    {
        $superAdmin = $this->creerSuperAdmin();
        $agence = $this->creerAgence();

        $this->actingAs($superAdmin)
            ->post(route('plateforme.agences.statut', $agence), [])
            ->assertSessionHasErrors('motif');

        $this->assertDatabaseHas('agences', [
            'id' => $agence->id,
            'active' => true,
        ]);
    }

    public function test_reactivation_efface_motif_suspension(): void
    {
        $superAdmin = $this->creerSuperAdmin();
        $agence = $this->creerAgence([
            'active' => false,
            'motif_suspension' => 'Suspension de test',
            'suspendue_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->post(route('plateforme.agences.statut', $agence))
            ->assertRedirect();

        $this->assertDatabaseHas('agences', [
            'id' => $agence->id,
            'active' => true,
            'motif_suspension' => null,
            'suspendue_at' => null,
        ]);
    }

    public function test_super_admin_ne_peut_pas_suspendre_sa_propre_agence(): void
    {
        $superAdmin = $this->creerSuperAdmin();
        $agence = $this->creerAgence();
        $superAdmin->forceFill(['agence_id' => $agence->id])->save();

        $this->actingAs($superAdmin)
            ->post(route('plateforme.agences.statut', $agence), [
                'motif' => 'Tentative de suspension',
            ])
            ->assertSessionHasErrors('agence');

        $this->assertDatabaseHas('agences', [
            'id' => $agence->id,
            'active' => true,
        ]);
    }

    private function creerAgence(array $attributs = []): Agence
    {
        return Agence::create(array_merge([
            'nom' => 'Agence test',
            'slug' => 'agence-test-'.Str::uuid(),
            'email' => 'agence-'.Str::uuid().'@example.test',
            'type' => 'agence',
            'active' => true,
        ], $attributs));
    }

    private function creerSuperAdmin(): User
    {
        $utilisateur = new User;
        $utilisateur->forceFill([
            'nom' => 'Super administrateur test',
            'email' => 'super-admin-'.Str::uuid().'@example.test',
            'role' => RoleUtilisateur::Administrateur,
            'statut_activation' => StatutActivation::Actif,
            'est_super_admin' => true,
        ])->save();

        return $utilisateur;
    }

    private function creerDemande(): DemandeAgence
    {
        return DemandeAgence::create([
            'nom_agence' => 'Agence en attente',
            'nom_contact' => 'Contact Test',
            'email' => 'agence-'.Str::uuid().'@example.test',
            'telephone' => '+228 90 00 00 02',
        ]);
    }
}
