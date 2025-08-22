<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->header('X-Request-Id', (string) Str::uuid());

        // dołącz do requestu i kontekstu logów
        $request->headers->set('X-Request-Id', $id);
        app('log')->withContext(['request_id' => $id]);

        $response = $next($request);

        // dołącz do odpowiedzi
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
