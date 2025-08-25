<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BenefitsRequest extends FormRequest
{
    /**
     * @return array<string, list<string|ValidationRule|Closure>>
     */
    public function rules(): array
    {
        return [
            'q'     => ['required', 'string', 'min:2', 'max:80'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function getQuery(): string
    {
        return (string) $this->input('q');
    }

    public function getLimit(): int
    {
        return (int) ($this->input('limit', 10));
    }
}