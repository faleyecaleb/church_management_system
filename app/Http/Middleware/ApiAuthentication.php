<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    /**
     * Handle an incoming request for API authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for API token in header
        $token = $request->bearerToken() ?? $request->header('X-API-Token');
        
        if (!$token) {
            return response()->json([
                'error' => 'API token required',
                'message' => 'Please provide a valid API token in Authorization header'
            ], 401);
        }

        // Validate token (you can implement your own token validation logic)
        $user = $this->validateApiToken($token);
        
        if (!$user) {
            return response()->json([
                'error' => 'Invalid API token',
                'message' => 'The provided API token is invalid or expired'
            ], 401);
        }

        // Set the authenticated user
        Auth::setUser($user);
        
        return $next($request);
    }

    /**
     * Validate API token and return user
     */
    private function validateApiToken($token)
    {
        // For now, use Laravel Sanctum tokens
        // You can implement custom token validation here
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if ($personalAccessToken && !$personalAccessToken->tokenable->trashed()) {
            return $personalAccessToken->tokenable;
        }
        
        return null;
    }
}