<?php
/**
 * Middleware dodający unikalny identyfikator do każdego requestu (X-Request-Id).
 * Ułatwia to śledzenie requestów w logach i debugowanie.
 * 
 * Jeśli klient nie poda nagłówka X-Request-Id, generujemy UUID.
 * Dodajemy ten nagłówek do requestu (żeby był dostępny w kontrolerach) i do kontekstu logów.
 * Na końcu dodajemy ten nagłówek do odpowiedzi.
 * 
 * Rejestracja middleware w kernelu: app/Http/Kernel.php
 * Dodajemy do grupy 'api' lub globalnie do wszystkich requestów.
 * 
 * Przykład użycia w logach:
 * Log::info('Some message', ['request_id' => $request->header('X-Request-Id')]);
 * 
 * Dzięki temu możemy łatwo powiązać logi z konkretnym requestem.
 */
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
