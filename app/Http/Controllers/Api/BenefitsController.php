<?php

namespace App\Http\Controllers\Api;

use App\Application\Dictionary\BenefitsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\BenefitsRequest;
use App\Http\Resources\BenefitResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BenefitsController extends Controller
{
    public function __invoke(BenefitsRequest $request, BenefitsService $service): AnonymousResourceCollection
    {
        $items = $service->suggest($request->getQuery(), $request->getLimit());

        return BenefitResource::collection($items);
    }
}
