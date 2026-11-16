<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_terms_and_privacy_pages_can_be_rendered(): void
    {
        $this->get(route('terms'))->assertOk();
        $this->get(route('privacy'))->assertOk();
    }
}
