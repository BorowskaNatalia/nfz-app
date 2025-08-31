<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LocalityResource extends JsonResource
{
    /** @param array<string,mixed> $resource */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? null,
            'name' => $this->resource['name'] ?? null,
        ];
    }
}
