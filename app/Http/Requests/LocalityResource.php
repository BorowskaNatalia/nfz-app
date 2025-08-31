<?php

namespace App\Http\Resources;

use App\Domain\DTO\LocalityDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LocalityDTO */
class LocalityResource extends JsonResource
{
    /** @return array{name:string} */
    public function toArray($request): array
    {
        return ['name' => $this->name];
    }
}
