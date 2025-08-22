<?php

namespace app\Contracts;

use app\Domain\DTO\SearchResultDTO;
use app\Domain\Value\SearchParams;

/**
 * Źródło danych o kolejkach (Terminy Leczenia).
 */
interface ItlClient
{
    /** @return array<SearchResultDTO> */
    public function search(SearchParams $params): array;
}
