<?php

use App\Http\Middleware\ApiKeyAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/api-auth-test', function (Request $request) {
    return response()->json([
        'ok' => true,
    ]);
})->middleware(ApiKeyAuthentication::class);
