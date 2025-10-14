<?php

namespace App\Http\Middleware;

use App\Models\Station;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateBosApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (!$authorization) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization header is required',
            ], 401);
        }

        // Parse Bearer token
        if (!preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authorization format. Expected: Bearer {token}',
            ], 401);
        }

        $apiKey = $matches[1];

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key cannot be empty',
            ], 401);
        }

        // Find station by API key
        $station = Station::findByApiKey($apiKey);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        if (!$station->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Station is not active',
            ], 403);
        }

        // Attach station to request for use in controllers
        $request->merge(['station' => $station]);

        return $next($request);
    }
}
