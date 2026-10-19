<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreErrorEventRequest;
use App\Services\ErrorRecorder;
use Illuminate\Http\JsonResponse;

class ErrorEventController extends Controller
{
    public function store(StoreErrorEventRequest $request, ErrorRecorder $recorder): JsonResponse
    {
        $apikey = $request->attributes->get('apikey');

        $data = $request->validated();

        $app = $apikey->app;

        $response = $recorder->record($app, $data);

        return new JsonResponse;
    }
}
