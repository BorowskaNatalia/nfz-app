<?php

namespace App\Infrastructure\Fake;

use App\Contracts\ItlClient;
use App\Domain\DTO\AppointmentDTO;
use App\Domain\DTO\ProviderDTO;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\SearchParams;

final class FakeItlClient implements ItlClient
{
    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params): array
    {
        // Udawane dane – 3 rekordy. Ignorujemy parametry na razie (OK na tym etapie).
        $p1 = new ProviderDTO(
            id: 'umw-100',
            name: 'Przychodnia Alfa',
            address: 'Warszawa, ul. Zdrowa 1',
            phone: '+48 111 111 111',
            website: null,
            lat: 52.2297,
            lng: 21.0122,
            forChildren: true,
        );

        $a1 = new AppointmentDTO(
            firstAvailableDate: new \DateTimeImmutable('2025-09-05'),
            queueSize: 10,
            priority: $params->priority,
            lastUpdated: new \DateTimeImmutable('2025-08-20T12:00:00Z'),
        );

        $p2 = new ProviderDTO(
            id: 'umw-200',
            name: 'Centrum Beta',
            address: 'Warszawa, ul. Szybka 2',
            phone: '+48 222 222 222',
            website: 'https://beta.example',
            lat: 52.24,
            lng: 21.0,
            forChildren: false,
        );

        $a2 = new AppointmentDTO(
            firstAvailableDate: new \DateTimeImmutable('2025-09-02'),
            queueSize: 25,
            priority: $params->priority,
            lastUpdated: new \DateTimeImmutable('2025-08-19T08:30:00Z'),
        );

        $p3 = new ProviderDTO(
            id: 'umw-300',
            name: 'Klinika Gamma',
            address: 'Warszawa, ul. Spokojna 3',
            phone: null,
            website: null,
            lat: 52.20,
            lng: 21.03,
            forChildren: true,
        );

        $a3 = new AppointmentDTO(
            firstAvailableDate: new \DateTimeImmutable('2025-09-10'),
            queueSize: 5,
            priority: $params->priority,
            lastUpdated: new \DateTimeImmutable('2025-08-21T15:00:00Z'),
        );

        return [
            new SearchResultDTO($p1, $a1),
            new SearchResultDTO($p2, $a2),
            new SearchResultDTO($p3, $a3),
        ];
    }
}
