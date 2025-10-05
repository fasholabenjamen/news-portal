<?php

namespace App\Services\Articles\Providers\NewYorkTimes;

use App\Contracts\Article\{
    ArticleDataContract,
    HasProviderIdentity,
    HasCategory,
    HasDescription,
    HasImageUrl,
    HasKeywords,
    HasSource,
};
use App\Enums\ArticleProviders;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ArticleData implements ArticleDataContract, HasImageUrl, HasCategory, HasKeywords, HasDescription, HasProviderIdentity, HasSource
{
    public function __construct(protected array $data)
    {
    }

    public function getProviderKey(): string
    {
        return ArticleProviders::NEW_YORK_TIMES->value;
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getContent(): string
    {
        return $this->data['abstract'];
    }

    public function getLink(): string
    {
        return $this->data['url'];
    }

    public function getPublishedAt(): Carbon
    {
        return Carbon::parse($this->data['published_date']);
    }

    public function getImageUrl(): ?string
    {
        return $this->data['image_url'];
    }

    public function getCategory(): ?string
    {
        return $this->data['section'];
    }

    public function getDesciption(): ?string
    {
        return $this->data['description'];
    }

    public function getProviderID(): string
    {
        return $this->data['asset_id'];
    }

    public function getSourceKey(): string
    {
        return Str::slug($this->data['source']);
    }

    public function getSourceName(): string
    {
        return $this->data['source'];
    }

    public function getKeywords(): string
    {
        return implode(',', $this->data['des_facet'] ?? '');
    }
}
