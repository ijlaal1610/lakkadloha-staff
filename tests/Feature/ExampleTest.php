<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/');
        // Expect a 302 redirect to the login page instead of a 200 OK
        $response->assertStatus(302); 
    }
}
