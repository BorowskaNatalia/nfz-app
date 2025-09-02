<?php

namespace App\Application\Search;

use App\Contracts\ItlClient;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\SearchParams;
use Illuminate\Support\Carbon;

final readonly class SearchService
{
    public function __construct(private ItlClient $itl) {}

    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params, string $sort = 'fastest'): array
    {
        $items = $this->itl->search($params);

        // filtr: dla dzieci (jeśli podano)
        if ($params->forChildren !== null) {
            $items = array_values(array_filter(
                $items,
                fn (SearchResultDTO $r) => $r->provider->forChildren === $params->forChildren
            ));
        }

        // filtr: max dni do pierwszego terminu (jeśli podano)
        if ($params->maxDays !== null) {
            $now = Carbon::now();
            $items = array_values(array_filter(
                $items,
                function (SearchResultDTO $r) use ($params, $now) {
                    $days = $now->diffInDays(Carbon::instance($r->appointment->firstAvailableDate), false);

                    return $days >= 0 && $days <= $params->maxDays;
                }
            ));
        }

        // filtr: miasto (jeśli podano)
        if ($params->city) {
            $needle = $this->normalize($params->city);

            $items = array_values(array_filter(
                $items,
                function (SearchResultDTO $r) use ($needle): bool {
                    $prov = $r->provider;

                    // 1) preferujemy dokładne (po normalizacji) dopasowanie locality
                    if (is_string($prov->locality) && $prov->locality !== '') {
                        if ($this->normalize($prov->locality) === $needle) {
                            return true;
                        }
                    }

                    // 2) fallback: szukaj w address (np. gdy locality puste)
                    return $this->matchCity($prov->address, $needle);
                }
            ));
        }

        // sort: najszybszy termin
        if ($sort === 'fastest') {
            usort(
                $items,
                fn (SearchResultDTO $a, SearchResultDTO $b) => $a->appointment->firstAvailableDate <=> $b->appointment->firstAvailableDate
            );
        }

        return $items;
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $map = ['ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ż' => 'z', 'ź' => 'z'];

        return strtr($s, $map);
    }

    private function matchCity(string $address, string $needle): bool
    {
        return str_contains($this->normalize($address), $this->normalize($needle));
    }
}
