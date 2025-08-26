<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $q
 * @property-read int $limit
 */
final class BenefitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string|int>> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function getQuery(): string
    {
        return trim((string) $this->validated('q'));
    }

    public function getLimit(): int
    {
        return (int) ($this->validated('limit') ?? 8);
    }
}
