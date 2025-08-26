<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\DTO\BenefitDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BenefitDTO */
final class BenefitResource extends JsonResource
{
    /** @param BenefitDTO $resource */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, string> */
    public function toArray($request): array
    {
        return ['name' => $this->name];
    }
}
