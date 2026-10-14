<?php

namespace App\Http\Middleware;

use App\Services\ApiKeyService;
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
            $service->verify($token);
        } catch (ApiKeyVerificationException $e) {
            return $this->authErrorResponse();
        }

        return $next($request);
    }

    private function authErrorResponse(): Response
    {
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
