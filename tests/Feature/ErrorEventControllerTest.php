<?php

namespace Tests\Feature;

use App\Services\ApiKeyService;
use App\Models\App;
use App\Models\Error;
use App\Models\ErrorEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payload,
            );

        $response->assertCreated();

        $eventId = $response->json("data.event_id");
        $errorId = $response->json("data.error_id");

        // Assert
        $error = Error::findOrFail($errorId);
        $event = ErrorEvent::findOrFail($eventId);


        $this->assertEquals($event->error_id, $errorId);
    }

    public function test_identical_errors_are_grouped(): void
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
        $responseFirst = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payload,
            );

        $responseFirst->assertCreated();

        $eventIdFirst = $responseFirst->json("data.event_id");
        $errorIdFirst = $responseFirst->json("data.error_id");

        $responseSecond = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payload,
            );

        $responseSecond->assertCreated();

        $eventIdSecond = $responseSecond->json("data.event_id");
        $errorIdSecond = $responseSecond->json("data.error_id");

        // Assert
        $errorFirst = Error::findOrFail($errorIdFirst);
        $errorSecond = Error::findOrFail($errorIdSecond);
        $this->assertEquals($errorFirst->id, $errorSecond->id);

        $eventFirst = ErrorEvent::findOrFail($eventIdFirst);
        $eventSecond = ErrorEvent::findOrFail($eventIdSecond);
        $this->assertEquals($eventFirst->error_id, $eventSecond->error_id);

        $this->assertDatabaseCount('error_events', 2);
        $this->assertDatabaseCount('errors', 1);
    }

    public function test_different_errors_are_separated(): void
    {
        // Arrange
        $service = new ApiKeyService();
        $app = App::factory()->create();
        $recordAndKey = $service->create($app, "Test key 1");
        $key = $recordAndKey['secret_key'];
        $url = '/api/v1/errors';
        $payloadFirst = [
            'exception' => 'RuntimeException',
            'message' => 'Something went wrong',
            'file' => '/app/Services/OrderService.php',
            'line' => 42,
            'env' => 'production',
        ];

        $payloadSecond = [
            'exception' => 'CustomException',
            'message' => 'Something went wrong',
            'file' => '/app/Services/OrderService.php',
            'line' => 42,
            'env' => 'production',
        ];
        // Act
        $responseFirst = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payloadFirst,
            );

        $responseFirst->assertCreated();

        $eventIdFirst = $responseFirst->json("data.event_id");
        $errorIdFirst = $responseFirst->json("data.error_id");

        $responseSecond = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payloadSecond,
            );

        $responseSecond->assertCreated();

        $eventIdSecond = $responseSecond->json("data.event_id");
        $errorIdSecond = $responseSecond->json("data.error_id");

        // Assert

        $errorFirst = Error::findOrFail($errorIdFirst);
        $errorSecond = Error::findOrFail($errorIdSecond);
        $this->assertNotEquals($errorFirst->id, $errorSecond->id);

        $eventFirst = ErrorEvent::findOrFail($eventIdFirst);
        $eventSecond = ErrorEvent::findOrFail($eventIdSecond);
        $this->assertNotEquals($eventFirst->error_id, $eventSecond->error_id);

        $this->assertDatabaseCount('error_events', 2);
        $this->assertDatabaseCount('errors', 2);
    }

    public function test_invalid_payload_is_rejected(): void
    {
        // Arrange
        $service = new ApiKeyService();
        $app = App::factory()->create();
        $recordAndKey = $service->create($app, "Test key 1");
        $key = $recordAndKey['secret_key'];
        $url = '/api/v1/errors';
        $payload = [
            'exception' => 1,
            'message' => null,
            'file' => false,
            'line' => 42,
            'env' => 'production',
        ];

        // Act
        $response = $this
            ->withToken($key)
            ->postJson(
                $url,
                $payload,
            );

        $response->assertUnprocessable();

        $this->assertDatabaseCount('errors', 0);
        $this->assertDatabaseCount('error_events', 0);
    }
}
