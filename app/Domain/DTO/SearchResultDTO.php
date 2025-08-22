<?php

namespace App\Domain\DTO;

final readonly class SearchResultDTO
{
    public function __construct(
        public ProviderDTO $provider,
        public AppointmentDTO $appointment,
        public float $distanceKm = 0.0, // do sortu "najbliżej"
    ) {}
}
