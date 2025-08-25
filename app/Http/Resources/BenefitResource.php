<?php

namespace App\Http\Resources;

use App\Domain\DTO\BenefitDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BenefitDTO */
class BenefitResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array{name: string}
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
        ];
    }
}