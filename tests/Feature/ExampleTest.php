<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // <-- Tambahkan ini
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // <-- Aktifkan trait ini

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/');
        // $response->dump();

        $response->assertStatus(200);
    }
}
