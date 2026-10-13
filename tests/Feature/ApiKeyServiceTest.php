<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\AppKey;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Key generates, it belongs to right app, is valid
     */
    public function test_api_key_creates_and_valid(): void
    {
        $app = App::factory()->create();

        $service = new ApiKeyService();

        $result = $service->newKey($app, 'Test key');

        $secretKey = $result['secret_key'];
        $record = $result['key_record'];

        $this->assertModelExists($record);

        $exploded = explode('live_', $secretKey);

        $secretParts = explode('_', $exploded[1]);

        $public = $secretParts[0];

        $secret = $secretParts[1];

        $key = AppKey::where('public_id', $public)->firstOrFail();

        $this->assertModelExists($key);

        $this->assertSame($app->id, $key->id);

	$this->assertSame($record->app_id, $key->app_id);

        $secretHash = hash('sha256', $secret);

        $this->assertTrue(hash_equals($key->secret_hash, $secretHash));
    }

    /**
     * Keys generated with different secrets
     */
    public function test_api_keys_different(): void
    {
	$firstApp = App::factory()->create();
    	$secondApp = App::factory()->create();

	$service = new ApiKeyService();

	$firstPair = $service->newKey($firstApp, "First key");
	$secondPair = $service->newKey($secondApp, "Second key");

	$this->assertNotSame($firstPair['secret_key'], $secondPair['secret_key']);
    }
}
