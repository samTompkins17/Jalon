<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_sees_the_vitrine_on_the_root(): void
    {
        $this->get('/')->assertOk()->assertSee('Chaque dossier');
    }

    public function test_root_redirects_guests_to_login_without_vitrine(): void
    {
        config(['app.vitrine' => false]);

        // Sans vitrine, un visiteur non connecté part vers la connexion.
        $this->get('/')->assertRedirect('/connexion');
    }

    public function test_guest_can_open_the_login_page(): void
    {
        $this->get('/connexion')->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_the_dashboard(): void
    {
        $this->get('/tableau-de-bord')->assertRedirect('/connexion');
    }
}
