<?php
/**
 * Serwis służący do podpowiedzi miejscowości (localities).
 * (endpoint /localities) i keszuje wynik, żeby nie męczyć API przy każdym wpisanym znaku.
 */
declare(strict_types=1);

namespace App\Application\Dictionary;

use App\Domain\DTO\LocalityDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class LocalitiesService
{
    /** @return array<LocalityDTO> */
    public function suggest(string $q, ?string $province = null, int $limit = 10): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 3) {
            return [];
        }

        $limit = max(1, min($limit, 25));
        $province = $this->normalizeProvince($province);

        $ttl = (int) config('itl.dictionary_ttl', 86400);
        $key = sprintf('itl:localities:suggest:%s:%s:%d',
            mb_strtolower($q),
            $province ?? '-',
            $limit
        );

        // nie utrwalamy pustek (hit tylko gdy mamy tablicę)
        $hit = Cache::get($key);
        if (is_array($hit)) {
            return $hit;
        }

        $query = [
            'name' => $q,
            'limit' => $limit,
            'page' => 1,
            'format' => 'json',
        ];
        if ($province !== null) {
            $query['province'] = $province;
        }

        $resp = Http::baseUrl((string) config('itl.base_url', 'https://api.nfz.gov.pl/app-itl-api'))
            ->acceptJson()
            ->timeout((int) config('itl.timeout', 6))
            ->retry(2, 200)
            ->get('/localities', $query)
            ->throw();

        /** @var mixed $rows */
        $rows = $resp->json('data', []);
        if (! is_array($rows)) {
            $rows = [];
        }

        // mapowanie defensywne + deduplikacja po nazwie
        $uniq = [];
        foreach ($rows as $row) {
            $name = null;

            if (is_string($row)) {
                // wariant: data[] = ["Warszawa", ...]
                $name = trim($row);
            } elseif (is_array($row)) {
                // wariant JSON:API
                $attr = $row['attributes'] ?? null;
                if (is_array($attr)) {
                    $name = $attr['name'] ?? ($attr['locality'] ?? null);
                }
                if (! is_string($name) || $name === '') {
                    $name = $row['name'] ?? null;
                }
            }

            if (is_string($name) && $name !== '') {
                $uniq[mb_strtolower($name)] = new LocalityDTO($name);
            }
        }

        $items = array_values($uniq);

        if ($items !== []) {
            Cache::put($key, $items, $ttl);
        }

        return $items;
    }

    private function normalizeProvince(?string $province): ?string
    {
        if ($province === null || trim($province) === '') {
            return null;
        }
        $p = trim($province);

        if (ctype_digit($p)) {
            $n = (int) $p;
            if ($n >= 1 && $n <= 16) {
                return sprintf('%02d', $n);
            }
        }
        if (preg_match('/^(0[1-9]|1[0-6])$/', $p) === 1) {
            return $p;
        }

        return null;
    }
}
