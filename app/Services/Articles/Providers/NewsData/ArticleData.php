<?php

namespace App\Services\Articles\Providers\NewsData;

use App\Contracts\Article\{
    ArticleDataContract,
    HasProviderIdentity,
    HasCategory,
    HasDescription,
    HasImageUrl,
    HasLanguage,
    HasSource,
};
use App\Enums\ArticleProviders;
use Carbon\Carbon;

class ArticleData implements ArticleDataContract, HasImageUrl, HasCategory, HasLanguage, HasDescription, HasProviderIdentity, HasSource
{
    public function __construct(protected array $data)
    {
    }

    public function getProviderKey(): string
    {
        return ArticleProviders::NEWS_DATA->value;
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getContent(): string
    {
        return $this->data['content'];
    }

    public function getLink(): string
    {
        return $this->data['link'];
    }

    public function getPublishedAt(): Carbon
    {
        return Carbon::parse($this->data['pubDate']);
    }

    public function getImageUrl(): ?string
    {
        return $this->data['image_url'];
    }

    public function getCategory(): ?string
    {
        return $this->parseCategory();
    }

    public function getLanguage(): ?string
    {
        return $this->data['language'];
    }

    public function getDesciption(): ?string
    {
        return $this->data['description'];
    }

    public function getProviderID(): string
    {
        return $this->data['article_id'];
    }

    public function getSourceKey(): string
    {
        return $this->data['source_id'];
    }

    public function getSourceName(): string
    {
        return $this->data['source_name'];
    }

    protected function parseCategory(): string
    {
        $categories = $this->data['category'];

        if (is_string($categories)) {
            return $categories;
        }

        return implode(' ', array_values(array_diff($categories, ['top'])));
    }
}
