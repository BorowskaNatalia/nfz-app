<?php

namespace App\Domain\Value;

final readonly class SearchParams
{
    public function __construct(
        public string $query,             // np. "kardiolog"
        public string $province,          // kod województwa, np. "07"
        public Priority $priority,        // STABLE / URGENT
        public ?bool $forChildren = null, // null = bez filtra
        public ?int $maxDays = null       // np. 60; null = bez filtra
    ) {}
}
