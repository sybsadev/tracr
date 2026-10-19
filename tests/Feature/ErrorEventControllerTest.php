<?php

namespace Tests\Feature;

use App\Services\ApiKeyService;
use App\Models\App;
use App\Models\AppKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ErrorEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_event_can_be_created(): void
    {
        // Arrange
        $service = new ApiKeyService();
        $app = App::factory()->create();
        $recordAndKey = $service->create($app, "Test key 1");
        $key = $recordAndKey['secret_key'];
        $url = '/api/v1/errors';
        $payload = [
            'exception' => 'RuntimeException',
            'message' => 'Something went wrong',
            'file' => '/app/Services/OrderService.php',
            'line' => 42,
            'env' => 'production',
        ];

        // Act
        $response = $this->postJson(
            $url,
            $payload,
            ["Authorization" => "Bearer {$key}"]
        );
        $event_id = $response->json("data.event_id");
        $error_id = $response->json("data.error_id");

        // Assert
        $this->assertDatabaseCount('errors', 1);
        $this->assertDatabaseCount('error_events', 1);
        $this->assertEquals(201, $response->status());
        $this->assertEquals(1, $event_id);
        $this->assertEquals(1, $error_id);
    }
}
