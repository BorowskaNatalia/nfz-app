<?php

/**
 * klasa, która zamienia obiekt  (SearchResultDTO) na stabilny JSON dla API.
 * Dzięki temu separuje warstwę domeny od warstwy prezentacji i mam jedno miejsce, w którym kontroluje kształt odpowiedzi.
 *
 * 1. Przyjmuje SearchResultDTO
 * 2. Mapuje pola z DTO na tablicę (która stanie się JSON-em)
 * 3. Formatuje typy złożone
 * 4. Standaryzuje nazewnictwo pod front-end
 */

namespace App\Http\Resources;

use App\Domain\DTO\SearchResultDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SearchResultDTO $resource
 */
class SearchResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $p = $this->resource->provider;
        $a = $this->resource->appointment;

        return [
            'provider' => [
                'id' => $p->id,
                'name' => $p->name,
                'locality' => $p->locality,
                'address' => $p->address,
                'phone' => $p->phone,
                'website' => $p->website,
                'forChildren' => $p->forChildren,
                'location' => [
                    'lat' => $p->lat,
                    'lng' => $p->lng,
                ],
            ],
            'appointment' => [
                'firstAvailableDate' => $a->firstAvailableDate->format('Y-m-d'),
                'queueSize' => $a->queueSize,
                'priority' => $a->priority->value,
                'lastUpdated' => $a->lastUpdated->format(DATE_ATOM),
            ],
            'distanceKm' => $this->resource->distanceKm,
        ];
    }
}
