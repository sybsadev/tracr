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
     * A basic feature test example.
     */
    public function test_api_key_creates_and_secure(): void
    {
        $app = App::factory()->create();

        $service = new ApiKeyService($app);

        $result = $service->new_key('Test key');

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

        $secret_hash = hash('sha256', $secret);

        $this->assertTrue(hash_equals($key->secret_hash, $secret_hash));
    }
}
