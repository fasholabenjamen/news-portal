<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        $data = parent::validationData();

        foreach (['categories_id', 'authors_id', 'sources_id'] as $key) {
            $data[$key] = $this->normalizeIdentifierList($data[$key] ?? null);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'categories_id' => ['nullable', 'array'],
            'categories_id.*' => ['integer', 'exists:categories,id'],
            'authors_id' => ['nullable', 'array'],
            'authors_id.*' => ['integer', 'exists:authors,id'],
            'sources_id' => ['nullable', 'array'],
            'sources_id.*' => ['integer', 'exists:sources,id'],
            'publish_date' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function categoryFilters(): array
    {
        return $this->filterNumericArray('categories_id');
    }

    public function authorFilters(): array
    {
        return $this->filterNumericArray('authors_id');
    }

    public function sourceFilters(): array
    {
        return $this->filterNumericArray('sources_id');
    }

    private function filterNumericArray(string $arrayKey): array
    {
        $validated = $this->validated();

        return collect(data_get($validated, $arrayKey, []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->unique()
            ->all();
    }

    private function normalizeIdentifierList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($segment) => $segment !== null && $segment !== ''));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map(
                fn (string $segment) => trim($segment),
                explode(',', $value)
            ), fn ($segment) => $segment !== ''));
        }

        if ($value !== null && $value !== '') {
            return [(string) $value];
        }

        return [];
    }
}
