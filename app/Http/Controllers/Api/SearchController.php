<?php
/**
 * Kontroler obsługujący endpoint /search do wyszukiwania świadczeniodawców i terminów.
 * Używa SearchService do właściwego wyszukiwania.
 * - odbiera żądanie z walidacją przez SearchRequest, woła SearchService->search(...) 
 * - zwraca wyniki jako JSON przez SearchResultResource::collection(...).
 *   przekazuje parametry do serwisu i opakowuje odpowiedź.
 */
namespace App\Http\Controllers\Api;

use App\Application\Search\SearchService;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController
{
    public function __invoke(SearchRequest $request, SearchService $service): AnonymousResourceCollection
    {
        $base = $request->toParams();
        $sort = $request->sort();

        $requested = $request->requestedDays();           // 30/60/90 albo null
        $hasHardMax = $request->filled('maxDays');        // jeśli user dał maxDays, nie relaksujemy

        $items = [];
        $applied = $base->maxDays;                        // domyślnie to, co w params (może null)
        $relaxation = [];

        if (! $hasHardMax && $requested !== null) {
            // zbuduj sekwencję prób: requested -> 60 -> 90 -> all (bez duplikatów)
            $candidates = [$requested, 60, 90, null];
            $uniq = [];
            foreach ($candidates as $c) {
                if (! in_array($c, $uniq, true)) {
                    $uniq[] = $c;
                }
            }

            foreach ($uniq as $candidate) {
                $try = new \App\Domain\Value\SearchParams(
                    query: $base->query,
                    province: $base->province,
                    priority: $base->priority,
                    forChildren: $base->forChildren,
                    city: $base->city,
                    maxDays: $candidate,
                );

                $items = $service->search($try, $sort);
                $applied = $candidate;

                // zapisujemy ścieżkę relaksacji do meta
                $relaxation[] = $candidate === null ? 'all' : (string) $candidate;

                if (count($items) > 0) {
                    break; // mamy wyniki ⇒ kończymy relaksację
                }
            }
        } else {
            // standardowa ścieżka (bez presetów lub z twardym maxDays)
            $items = $service->search($base, $sort);
            $applied = $base->maxDays;
        }

        $lastUpdated = null;
        if (! empty($items)) {
            $lastUpdated = max(array_map(
                fn ($r) => $r->appointment->lastUpdated->format(DATE_ATOM),
                $items
            ));
        }

        $extraMeta = [
            'count' => count($items),
            'lastUpdated' => $lastUpdated,
        ];

        // meta.filters tylko gdy użyto presetów „days”
        if ($requested !== null && ! $hasHardMax) {
            $extraMeta['filters'] = [
                'requestedMaxDays' => $requested,
                'appliedMaxDays' => $applied === null ? null : (int) $applied,
                'relaxation' => $relaxation,
            ];
        }

        return SearchResultResource::collection($items)
            ->additional(['meta' => $extraMeta]);
    }
}