<?php

use App\Domain\DTO\AppointmentDTO;
use App\Domain\DTO\ProviderDTO;
use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\Priority;

it('builds SearchResultDTO with correct types', function () {
    $provider = new ProviderDTO(
        id: 'umw-123',
        name: 'Przychodnia X',
        address: 'Warszawa, ul. Przykładowa 1',
        phone: '+48 123 456 789',
        website: null,
        lat: 52.23,
        lng: 21.01,
        forChildren: true,
    );

    $appointment = new AppointmentDTO(
        firstAvailableDate: new DateTimeImmutable('2025-09-05'),
        queueSize: 12,
        priority: Priority::STABLE,
        lastUpdated: new DateTimeImmutable('2025-08-22T10:00:00Z'),
    );

    $result = new SearchResultDTO($provider, $appointment, distanceKm: 3.4);

    expect($result->provider->name)->toBe('Przychodnia X')
        ->and($result->appointment->priority)->toBe(Priority::STABLE)
        ->and($result->distanceKm)->toBeFloat()->toBe(3.4);
});
