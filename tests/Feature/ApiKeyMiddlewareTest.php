<?php

namespace Tests\Feature;

use App\Http\Middleware\ApiKeyAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    /**
     * Main middleware test
     */
    public function test_middleware_returns_unauthenticated(): void
    {

    }
}
