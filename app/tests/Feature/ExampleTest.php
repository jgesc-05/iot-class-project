<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La landing redirige a /login si no hay sesion activa.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
