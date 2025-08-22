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

        return SearchResultResource::collection($items);
    }
}
