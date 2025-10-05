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
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'categories_id' => ['nullable', 'array'],
            'categories_id.*' => ['integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'authors_id' => ['nullable', 'array'],
            'authors_id.*' => ['integer', 'exists:authors,id'],
            'source_id' => ['nullable', 'integer', 'exists:sources,id'],
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
        return $this->filterNumericArray('categories_id', 'category_id');
    }

    public function authorFilters(): array
    {
        return $this->filterNumericArray('authors_id', 'author_id');
    }

    public function sourceFilters(): array
    {
        return $this->filterNumericArray('sources_id', 'source_id');
    }

    private function filterNumericArray(string $arrayKey, ?string $singleKey = null): array
    {
        $validated = $this->validated();

        return collect(data_get($validated, $arrayKey, []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->when($singleKey && filled(data_get($validated, $singleKey)), function ($collection) use ($validated, $singleKey) {
                $collection->push((int) data_get($validated, $singleKey));

                return $collection;
            })
            ->values()
            ->unique()
            ->all();
    }
}
