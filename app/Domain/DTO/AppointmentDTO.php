<?php

namespace App\Domain\DTO;

use App\Domain\Value\Priority;
use DateTimeImmutable;

final readonly class AppointmentDTO
{
    public function __construct(
        public DateTimeImmutable $firstAvailableDate, // data pierwszej dostępnej wizyty
        public int $queueSize,                         // liczba oczekujących
        public Priority $priority,                    // STABLE/URGENT
        public DateTimeImmutable $lastUpdated,        // kiedy zaktualizowano
    ) {}
}
