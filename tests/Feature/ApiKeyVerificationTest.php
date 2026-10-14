<?php

namespace Tests\Feature;

use App\Exceptions\ApiKeyVerificationException;
use App\Models\App;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Check that api key verifies and returns record
     */
    public function test_api_key_verification_works(): void
    {
        $app = App::factory()->create();

        $apiKeyService = new ApiKeyService;

        $key = $apiKeyService->create($app, 'Test key');

        $verified = $apiKeyService->verify($key['secret_key']);

        $this->assertSame($verified->id, $key['key_record']->id);
    }

    public function test_api_key_expiration(): void
    {
        $app = App::factory()->create();

        $apiKeyService = new ApiKeyService;

        $key = $apiKeyService->create($app, 'Test key');

        $key['key_record']->expires_at = now()->subMinutes(15);

	$key['key_record']->save();

        $this->expectException(ApiKeyVerificationException::class);

        $apiKeyService->verify($key['secret_key']);
    }

    public function test_api_key_disabled(): void
    {
        $app = App::factory()->create();

        $apiKeyService = new ApiKeyService;

        $key = $apiKeyService->create($app, 'Test key');

        $key['key_record']->is_active = false;

	$key['key_record']->save();

        $this->expectException(ApiKeyVerificationException::class);

        $apiKeyService->verify($key['secret_key']);
    }

    public function test_api_key_app_disabled(): void
    {
        $app = App::factory()->create();

        $apiKeyService = new ApiKeyService;

        $key = $apiKeyService->create($app, 'Test key');

        $key['key_record']->app->is_active = false;

	$key['key_record']->app->save();

        $this->expectException(ApiKeyVerificationException::class);

        $apiKeyService->verify($key['secret_key']);
    }

    public function test_api_key_user_disabled(): void
    {
        $app = App::factory()->create();

        $apiKeyService = new ApiKeyService;

        $key = $apiKeyService->create($app, 'Test key');

        $key['key_record']->app->user->is_active = false;


	$key['key_record']->app->user->save();

        $this->expectException(ApiKeyVerificationException::class);

        $apiKeyService->verify($key['secret_key']);
    }
}
