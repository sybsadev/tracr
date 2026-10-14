<?php

namespace App\Http\Middleware;

use App\Services\ApiKeyService;
use App\Exceptions\ApiKeyVerificationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->authErrorResponse();
        }

        $service = new ApiKeyService;

        try {
            $verified = $service->verify($token);
        } catch (ApiKeyVerificationException $e) {
            return $this->authErrorResponse();
        }

	$request->attributes->set('apiKey', $verified);

        return $next($request);
    }

    private function authErrorResponse(): Response
    {
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
