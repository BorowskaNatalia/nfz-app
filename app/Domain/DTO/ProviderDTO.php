<?php

namespace App\Domain\DTO;

final readonly class ProviderDTO
{
    public function __construct(
        public string $id,            // identyfikator placówki w bazie NFZ
        public string $name,          // nazwa placówki
        public string $address,       // pełny adres
        public ?string $locality,
        public ?string $phone,        // telefon (może brakować)
        public ?string $website,      // strona www (może brakować)
        public ?float $lat, // szerokość geogr.
        public ?float $lng,           // długość geogr.
        public bool $forChildren,     // czy świadczenie dla dzieci
    ) {}
}
