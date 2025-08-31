<?php

namespace App\Infrastructure\Http;

use App\Contracts\ItlClient;
use App\Domain\DTO\AppointmentDTO;
use App\Domain\DTO\ProviderDTO;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\Priority;
use App\Domain\Value\SearchParams;
use Illuminate\Support\Facades\Http;

final class HttpItlClient implements ItlClient
{
    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params): array
    {
        $case = $params->priority === Priority::URGENT ? 2 : 1; // 1=stabilny, 2=pilny

        $query = [
            'case' => $case,
            'province' => $params->province,
            'benefit' => $params->query,
            'locality' => $params->city ?: null,
            'page' => 1,
            'limit' => 10,
            'format' => 'json',
        ];

        $resp = Http::baseUrl((string) config('itl.base_url'))
            ->timeout((int) config('itl.timeout'))
            ->retry((int) config('itl.retry.times'), (int) config('itl.retry.sleep_ms'))
            ->get('/queues', $query)
            ->throw();

        /** @var array{data?: array<int, array<string, mixed>>} $json */
        $json = $resp->json();
        $data = $json['data'] ?? [];

        $out = [];
        foreach ($data as $row) {
            $dto = $this->mapQueueItem($row, $params->priority);
            if ($dto !== null) {
                $out[] = $dto;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function mapQueueItem(array $row, Priority $priority): ?SearchResultDTO
    {
        /** @var array<string, mixed>|null $attr */
        $attr = $row['attributes'] ?? null;
        if (! is_array($attr)) {
            return null;
        }

        // Provider
        $providerId = (string) ($attr['provider-code'] ?? $row['id'] ?? '');
        $providerName = (string) ($attr['provider'] ?? '');

        // DTO wymaga string → dajemy pusty string, gdy brak
        $address = is_string($attr['address'] ?? null) ? $attr['address'] : '';

        // DTO pozwala na null dla telefonu/website
        $phone = is_string($attr['phone'] ?? null) ? $attr['phone'] : null;
        $website = null;

        // DTO wymaga bool → mapujemy Y/N, brak => false (defensywnie)
        $forChildren = match ($attr['benefits-for-children'] ?? null) {
            'Y' => true,
            'N' => false,
            default => false,
        };

        $lat = isset($attr['latitude']) && $attr['latitude'] !== null ? (float) $attr['latitude'] : null;
        $lng = isset($attr['longitude']) && $attr['longitude'] !== null ? (float) $attr['longitude'] : null;

        $provider = new ProviderDTO(
            id: $providerId,
            name: $providerName,
            address: $address,
            phone: $phone,
            website: $website,
            lat: $lat,
            lng: $lng,
            forChildren: $forChildren,
        );

        // Appointment
        /** @var array<string, mixed>|null $dates */
        $dates = $attr['dates'] ?? null;
        $dateStr = is_array($dates) ? ($dates['date'] ?? null) : null;
        if (! is_string($dateStr) || $dateStr === '') {
            return null; // bez daty nie zwracamy rekordu
        }
        $firstDate = new \DateTimeImmutable($dateStr);

        $updatedStr = is_array($dates) ? ($dates['date-situation-as-at'] ?? null) : null;
        $lastUpdated = is_string($updatedStr) && $updatedStr !== ''
            ? new \DateTimeImmutable($updatedStr)
            : new \DateTimeImmutable;

        // DTO wymaga int → brak/śmieć => 0
        $awaiting = $attr['statistics']['provider-data']['awaiting'] ?? null;
        $queueSize = is_int($awaiting) ? $awaiting : (is_numeric($awaiting) ? (int) $awaiting : 0);

        $appointment = new AppointmentDTO(
            firstAvailableDate: $firstDate,
            queueSize: $queueSize,
            priority: $priority,
            lastUpdated: $lastUpdated,
        );

        return new SearchResultDTO(
            provider: $provider,
            appointment: $appointment,
            distanceKm: 0.0,
        );
    }
}
