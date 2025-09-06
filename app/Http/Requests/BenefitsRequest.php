<?php
/*
Routing → FormRequest (authorize + rules, ewentualnie trim) → [walidacja OK?] → Controller (woła serwis, zwraca JSON).
 klasa wejścia. Odpowiada za autoryzację i walidację danych z HTTP (query/body),
 ich oczyszczenie i wygodne gettery (getQuery(), getLimit()). 
 Jeśli walidacja nie przejdzie, kontroler się w ogóle nie wykona.
*/
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
