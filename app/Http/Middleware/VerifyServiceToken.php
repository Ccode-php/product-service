<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyServiceToken
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {

            return response()->json([
                'error' => 'Token required'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $header);

        try {

            $publicKey = file_get_contents(
                storage_path('oauth-public.key')
            );

            $decoded = JWT::decode(
                $token,
                new Key($publicKey, 'RS256')
            );

            /*
            |--------------------------------------------------------------------------
            | SERVICE TOKEN CHECK
            |--------------------------------------------------------------------------
            */

            if (!isset($decoded->aud)) {

                return response()->json([
                    'error' => 'Service token required'
                ], 403);
            }

            $request->merge([
                'service_token' => [
                    'client_id' => $decoded->sub ?? null,
                    'aud' => $decoded->aud ?? null,
                    'scopes' => $decoded->scopes ?? [],
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'error' => 'Invalid service token',
                'message' => $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}