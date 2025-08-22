<?php

namespace App\Contracts;

use App\Domain\DTO\SearchResultDTO;
use App\Domain\Value\SearchParams;

/**
 * Źródło danych o kolejkach (Terminy Leczenia).
 */
interface ItlClient
{
    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params): array;
}
