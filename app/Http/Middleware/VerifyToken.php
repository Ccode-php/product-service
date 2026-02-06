<?php

namespace App\Http\Middleware;


use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyToken
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $token = str_replace('Bearer ', '', $header);

        try {
            $publicKey = file_get_contents(env('PUBLIC_KEY'));
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
