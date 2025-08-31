<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class LocalitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|ValidationRule|Closure>> */
    public function rules(): array
    {
        return [
            // NFZ: min 3 znaki
            'q' => ['required', 'string', 'min:3', 'max:80'],
            // 2-znakowy kod województwa; opcjonalny
            'province' => ['sometimes', 'nullable', 'string', 'size:2'],
            // NFZ: limit ≤ 25
            'limit' => ['sometimes', 'integer', 'min:1', 'max:25'],
        ];
    }

    public function getQuery(): string
    {
        return trim((string) $this->validated('q'));
    }

    public function getProvince(): ?string
    {
        $p = $this->validated('province') ?? null;

        return is_string($p) && $p !== '' ? $p : null;
    }

    public function getLimit(): int
    {
        return (int) ($this->validated('limit') ?? 10);
    }
}
