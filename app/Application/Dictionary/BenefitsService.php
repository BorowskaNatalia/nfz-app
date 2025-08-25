<?php

namespace App\Application\Dictionary;

use App\Domain\DTO\BenefitDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class BenefitsService
{
    /** @return array<BenefitDTO> */
    public function suggest(string $q, int $limit = 10): array
    {
        $q = trim($q);
        $limit = max(1, min($limit, 50));

        $ttl = (int) config('itl.dictionary_ttl', 86400); // 24h
        $key = sprintf('itl:benefits:suggest:%s:%d', mb_strtolower($q), $limit);

        return Cache::remember($key, $ttl, function () use ($q, $limit) {
            $resp = Http::baseUrl((string) config('itl.base_url', 'https://api.nfz.gov.pl/app-itl-api'))
                ->acceptJson()
                ->timeout((int) config('itl.timeout', 6))
                ->retry([200, 500, 1000]) // ms; prosty backoff
                ->get('/benefits', [
                    'name' => $q,
                    'limit' => $limit,
                    'format' => 'json',
                    'page' => 1,
                ])
                ->throw();

            /** @var array{data?: array<int, array<string, mixed>>} $json */
            $json = $resp->json();
            $rows = $json['data'] ?? [];

            $out = [];
            foreach ($rows as $row) {
                // JSON:API -> attributes.benefit (nazwa świadczenia)
                $attr = $row['attributes'] ?? null;
                $name = is_array($attr ?? null) ? ($attr['benefit'] ?? null) : null;
                if (is_string($name) && $name !== '') {
                    $out[] = new BenefitDTO($name);
                }
            }

            return $out;
        });
    }
}
