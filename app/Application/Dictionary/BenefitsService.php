<?php
/**
 * Serwis służący do podpowiedzi świadczeń (benefits).
 * (endpoint /benefits) i keszuje wynik, żeby nie męczyć API przy każdym wpisanym znaku.
 */

namespace App\Application\Dictionary;

use App\Domain\DTO\BenefitDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class BenefitsService
{
    /** @return array<BenefitDTO> */
    public function suggest(string $q, int $limit = 10): array
    {
        $q = trim($q);

        if (mb_strlen($q) < 2) {
            return [];
        }

        $limit = max(1, min($limit, 50));

        $ttl = (int) config('itl.dictionary_ttl', 86400); // 24h
        $key = sprintf('itl:benefits:suggest:%s:%d', mb_strtolower($q), $limit);

        return Cache::remember($key, $ttl, function () use ($q, $limit) {
            try {
                $resp = Http::baseUrl((string) config('itl.base_url', 'https://api.nfz.gov.pl/app-itl-api'))
                    ->acceptJson()
                    ->timeout((int) config('itl.timeout', 6))
                    ->retry(2, 300) // prosty backoff
                    ->get('/benefits', [
                        'name' => $q,
                        'limit' => $limit,
                        'page' => 1,
                    ]);

                if (! $resp->successful()) {
                    Log::warning('itl.benefits_http_error', [
                        'status' => $resp->status(),
                        'body' => mb_substr($resp->body(), 0, 500),
                    ]);

                    return [];
                }

                /** @var array{data?: mixed} $json */
                $json = $resp->json();
                $rows = is_array($json['data'] ?? null) ? $json['data'] : [];

                // 3) obsłuż stringi i JSON:API
                $names = [];
                foreach ($rows as $row) {
                    if (is_string($row)) {
                        $name = trim($row);
                    } elseif (is_array($row)) {
                        $attr = $row['attributes'] ?? [];
                        $name = trim((string) ($attr['benefit'] ?? ($row['name'] ?? '')));
                    } else {
                        $name = '';
                    }
                    if ($name !== '') {
                        $names[$name] = true;
                    }
                }

                return array_map(fn (string $n) => new BenefitDTO($n), array_keys($names));
            } catch (\Throwable $e) {
                Log::error('itl.benefits_exception', ['msg' => $e->getMessage()]);
                return [];
            }
        });
    }
}