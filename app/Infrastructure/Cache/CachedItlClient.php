<?php

namespace App\Infrastructure\Cache;

use App\Contracts\ItlClient;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\SearchParams;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final readonly class CachedItlClient implements ItlClient
{
    public function __construct(private ItlClient $inner) {}

    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params): array
    {
        $key = $this->makeKey($params);
        $ttl = (int) config('itl.cache_ttl', 900);

        return Cache::remember($key, $ttl, function () use ($params, $key) {
            Log::info('itl.cache.miss', ['key' => $key]);

            return $this->inner->search($params);
        });
    }

    private function makeKey(SearchParams $p): string
    {
        $payload = [
            'v' => 1,
            'query' => $p->query,
            'province' => $p->province,
            'priority' => $p->priority->value,
            'kids' => $p->forChildren,
            'maxDays' => $p->maxDays,
            'city' => $p->city,
        ];

        try {
            // Gwarantuje string albo wyjątek (bez false) → PHPStan zadowolony
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // awaryjny fallback – i tak zwróci string
            $json = serialize($payload);
        }

        return 'itl:'.sha1($json);
    }
}
