<?php

namespace App\Http\Controllers\Api;

use App\Models\ErrorEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreErrorEventRequest;
use App\Services\ErrorRecorder;
use Illuminate\Http\JsonResponse;

class ErrorEventController extends Controller
{
    public function store(StoreErrorEventRequest $request, ErrorRecorder $recorder): JsonResponse
    {
        $apikey = $request->attributes->get('apiKey');

        $data = $request->validated();

        $app = $apikey->app;

        $event = $recorder->record($app, $data);

        return $this->validResponse($event);
    }

    private function validResponse(ErrorEvent $event): JsonResponse
    {
        $data = [
            'success' => true,
            'message' => 'Event created',
            'data' => [
                'error_id' => $event->error_id,
                'event_id' => $event->id,
            ],
        ];

        return new JsonResponse($data, 201);
    }
}
