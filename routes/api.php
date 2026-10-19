<?php

use App\Http\Controllers\Api\ErrorEventController;
use App\Http\Middleware\ApiKeyAuthentication;
use Illuminate\Support\Facades\Route;

Route::middleware(ApiKeyAuthentication::class)->group(function () {
    Route::post('v1/errors', [ErrorEventController::class, 'store']);
});
