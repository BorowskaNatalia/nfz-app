<?php

namespace App\Domain\DTO;

use JsonSerializable;

final readonly class LocalityDTO implements JsonSerializable
{
    public function __construct(
        public string $name,
    ) {}

    /** @return array{name: string} */
    public function jsonSerialize(): array
    {
        return ['name' => $this->name];
    }
}
