<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Value\{Priority, SearchParams};

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'        => ['required', 'string', 'min:2'],
            'province' => ['required', 'string', 'size:2'],
            'priority' => ['required', 'in:stable,urgent'],

            // 'sometimes' => waliduj tylko jeśli pole przyszło
            'kids'     => ['sometimes', 'boolean'],

            // przyjmujemy null/puste, gdy nie ustawione
            'maxDays'  => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
            'days'     => ['sometimes', 'nullable', 'in:30,60,90'],

            'sort'     => ['sometimes', 'in:fastest'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Zamień puste stringi na null
        $this->merge([
            'days'    => $this->filled('days') && $this->input('days') !== '' ? $this->input('days') : null,
            'maxDays' => $this->filled('maxDays') && $this->input('maxDays') !== '' ? $this->input('maxDays') : null,
        ]);

        // Upewnij się, że kids jest bool (Laravelowa konwersja)
        if ($this->has('kids')) {
            $this->merge(['kids' => $this->boolean('kids')]);
        }
    }

    public function sort(): string
    {
        return $this->input('sort', 'fastest');
    }

    public function requestedDays(): ?int
    {
        return $this->filled('days') ? (int) $this->input('days') : null;
    }

    public function toParams(): SearchParams
    {
        return new SearchParams(
            query: $this->string('q'),
            province: $this->string('province'),
            priority: $this->input('priority') === 'urgent' ? Priority::URGENT : Priority::STABLE,
            forChildren: $this->has('kids') ? $this->boolean('kids') : null,
            maxDays: $this->filled('maxDays') ? (int) $this->input('maxDays') : null,
        );
    }
}