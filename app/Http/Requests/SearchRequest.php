<?php

namespace App\Http\Requests;

use App\Domain\Value\Priority;
use App\Domain\Value\SearchParams;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // brak auth w MVP
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2'],
            'province' => ['required', 'string', 'size:2'],
            'priority' => ['required', 'in:stable,urgent'],
            'kids' => ['nullable', 'boolean'],
            'maxDays' => ['nullable', 'integer', 'min:1', 'max:365'],
            'days' => ['nullable', 'in:30,60,90'],
            'sort' => ['nullable', 'in:fastest'],
        ];
    }

    public function toParams(): SearchParams
    {
        $priority = $this->input('priority') === 'urgent' ? Priority::URGENT : Priority::STABLE;

        return new SearchParams(
            query: (string) $this->input('q'),
            province: (string) $this->input('province'),
            priority: $priority,
            forChildren: $this->has('kids') ? $this->boolean('kids') : null,
            maxDays: $this->filled('maxDays') ? (int) $this->input('maxDays') : null,
        );
    }

    public function requestedDays(): ?int
    {
        if (! $this->filled('days')) {
            return null;
        }

        return (int) $this->input('days');
    }

    public function sort(): string
    {
        return (string) $this->input('sort', 'fastest');
    }
}
