<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('app.api_key') ?? env('API_KEY');

        if (empty($apiKey)) {
            return $this->errorResponse('API Key configuration missing on server.', 500);
        }

        // Permitir el token tanto en la cabecera 'X-API-KEY' como en 'Authorization: Bearer <token>'
        $requestKey = $request->header('X-API-KEY') ?? $request->bearerToken();

        if ($requestKey !== $apiKey) {
            return $this->errorResponse('Unauthorized. Invalid API Key.', 401);
        }

        return $next($request);
    }
}
