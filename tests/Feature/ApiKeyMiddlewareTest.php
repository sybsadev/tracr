<?php

namespace Tests\Feature;

use App\Models\App;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Main middleware test for blank request
     */
    public function test_middleware_blank_request_401(): void
    {
        $response = $this->get('api/api-auth-test');

        $response->assertStatus(401);
    }

    /**
     * test for invalid secret
     */
    public function test_middleware_invalid_key_401(): void
    {
        $invalidKey = 'invalid_key';

        $response = $this
            ->withToken($invalidKey)
            ->getJson('api/api-auth-test');

        $response->assertStatus(401);
    }

    /**
     * test for valid secret
     */
    public function test_middleware_valid_key_200(): void
    {

        $app = App::factory()->create();

        $service = new ApiKeyService;

        $key = $service->create($app, 'Test key');

        $response = $this
            ->withToken($key['secret_key'])
            ->getJson('api/api-auth-test');

        $response->assertStatus(200);
    }

    /**
     * test for disabled secret
     */
    public function test_middleware_disabled_key_401(): void
    {

        $app = App::factory()->create();

        $service = new ApiKeyService;

        $key = $service->create($app, 'Test key');

        $record = $key['key_record'];

        $record->is_active = false;

        $record->save();

        $response = $this
            ->withToken($key['secret_key'])
            ->getJson('api/api-auth-test');

        $response->assertStatus(401);
    }
}
