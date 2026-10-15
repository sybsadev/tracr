<?php

namespace Tests\Feature;

use App\Http\Middleware\ApiKeyAuthentication;
use App\Models\App;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api-auth-test', function (Request $request) {
            return response()->json([
                'api_key_id' => $request->attributes
                    ->get('apiKey')
                    ?->id,
            ], 200);
        })->middleware(ApiKeyAuthentication::class);
    }

    /**
     * Middleware test for api auth
     */
    public function test_middleware_blank_request_401(): void
    {
        $response = $this->get('/api-auth-test');

        $response->assertStatus(401);
    }

    public function test_middleware_invalid_key_401(): void
    {
        $invalidKey = 'invalid_key';

        $response = $this
            ->withToken($invalidKey)
            ->getJson('/api-auth-test');

        $response->assertStatus(401);
    }

    public function test_middleware_valid_key_200(): void
    {

        $app = App::factory()->create();

        $service = new ApiKeyService;

        $key = $service->create($app, 'Test key');

        $response = $this
            ->withToken($key['secret_key'])
            ->getJson('/api-auth-test');

        $response
            ->assertOk()
            ->assertJsonPath('api_key_id', $key['key_record']->id);
    }

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
            ->getJson('/api-auth-test');

        $response->assertStatus(401);
    }
}
