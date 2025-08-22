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

        // sort: najszybszy termin
        if ($sort === 'fastest') {
            usort(
                $items,
                fn (SearchResultDTO $a, SearchResultDTO $b) => $a->appointment->firstAvailableDate <=> $b->appointment->firstAvailableDate
            );
        }

        return $items;
    }
}
