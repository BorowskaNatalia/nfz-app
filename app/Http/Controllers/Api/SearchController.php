<?php

namespace App\Http\Controllers\Api;

use App\Application\Search\SearchService;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController
{
    public function __invoke(SearchRequest $request, SearchService $service): AnonymousResourceCollection
    {
        $items = $service->search($request->toParams(), $request->sort());

        $lastUpdated = null;
        if (! empty($items)) {
            $lastUpdated = max(array_map(
                fn ($r) => $r->appointment->lastUpdated->format(DATE_ATOM),
                $items
            ));
        }

        return SearchResultResource::collection($items)
            ->additional([
                'meta' => [
                    'count' => count($items),
                    'lastUpdated' => $lastUpdated, // null, gdy brak wyników
                ],
            ]);
    }
}
