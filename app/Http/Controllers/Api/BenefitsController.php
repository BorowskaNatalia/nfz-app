<?php
/**
 * Kontroler obsługujący endpoint /benefits do podpowiedzi świadczeń (benefits).
 * Używa BenefitsService do pobierania i keszowania wyników.
 * - odbiera żądanie z walidacją przez BenefitsRequest, woła BenefitsService->suggest(...) 
 * - zwraca wyniki jako JSON przez BenefitResource::collection(...).
 *   przekazuje parametry do serwisu i opakowuje odpowiedź.
 */
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
