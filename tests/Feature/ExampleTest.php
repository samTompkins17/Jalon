<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_the_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/tableau-de-bord');
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
