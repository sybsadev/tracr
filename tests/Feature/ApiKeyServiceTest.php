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

        $result = $service->new_key($app, 'Test key');

        $secret_key = $result['secret_key'];
        $record = $result['key_record'];

        $this->assertModelExists($record);

        $exploded = explode('live_', $secret_key);

        $secret_parts = explode('_', $exploded[1]);

        $public = $secret_parts[0];

        $secret = $secret_parts[1];

        $key = AppKey::where('public_id', $public)->firstOrFail();

        $this->assertModelExists($key);

        $this->assertSame($record->id, $key->id);

	$this->assertSame($record->app_id, $key->app_id);

        $secret_hash = hash('sha256', $secret);

        $this->assertTrue(hash_equals($key->secret_hash, $secret_hash));
    }

    /**
     * Keys generated with different secrets
     */
    public function test_api_keys_different(): void
    {
	$first_app = App::factory()->create();
    	$second_app = App::factory()->create();

	$service = new ApiKeyService();

	$first_pair = $service->new_key($first_app, "First key");
	$second_pair = $service->new_key($second_app, "Second key");

	$this->assertNotSame($first_pair['key_record']->public_id, $second_pair['key_record']->public_id);
    }
}
