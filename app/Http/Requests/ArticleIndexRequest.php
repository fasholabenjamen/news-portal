<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string'],
            'author' => ['nullable', 'string'],
            'source_id' => ['nullable', 'integer', 'exists:sources,id'],
            'publish_date' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function categoryFilters(): array
    {
        return $this->filterStringArray('categories');
    }

    public function authorFilters(): array
    {
        return $this->filterStringArray('authors');
    }

    /**
     * @return array<int, int>
     */
    public function sourceFilters(): array
    {
        return $this->filterNumericArray('sources');
    }

    /**
     * @return array<int, string>
     */
    private function filterStringArray(string $key): array
    {
        return collect(data_get($this->validated(), $key, []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (string) $value)
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function filterNumericArray(string $key): array
    {
        return collect(data_get($this->validated(), $key, []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();
    }
}
