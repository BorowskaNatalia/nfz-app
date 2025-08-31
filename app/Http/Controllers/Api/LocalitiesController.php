<?php

namespace App\Http\Controllers\Api;

use App\Application\Dictionary\LocalitiesService;
use App\Domain\DTO\LocalityDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LocalitiesController extends Controller
{
    public function __construct(
        private readonly LocalitiesService $service,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string'],
            'province' => ['required', 'string', 'size:2'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $q = (string) ($validated['q'] ?? '');
        $province = (string) $validated['province'];
        $limit = (int) ($validated['limit'] ?? 10);

        /** @var array<int, LocalityDTO> $items */
        $items = $this->service->suggest($q, $province, $limit);

        /** @var array<int, string> $payload */
        $payload = array_map(static fn (LocalityDTO $dto): string => $dto->name, $items);

        return response()->json([
            'meta' => ['count' => count($payload)],
            'data' => $payload,
        ]);
    }
}
