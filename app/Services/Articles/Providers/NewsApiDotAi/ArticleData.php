<?php

namespace App\Services\Articles\Providers\NewsApiDotAi;

use App\Contracts\Article\{
    ArticleDataContract,
    HasProviderIdentity,
    HasDescription,
    HasImageUrl,
    HasLanguage,
    HasSource,
    HasAuthorName
};
use App\Enums\ArticleProviders;
use Carbon\Carbon;

class ArticleData implements ArticleDataContract, HasImageUrl, HasLanguage, HasDescription, HasProviderIdentity, HasSource, HasAuthorName
{
    public function __construct(protected array $data)
    {
    }

    public function getProviderKey(): string
    {
        return ArticleProviders::NEWS_API_DOT_AI->value;
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getContent(): string
    {
        return $this->data['body'];
    }

    public function getLink(): string
    {
        return $this->data['url'];
    }

    public function getPublishedAt(): Carbon
    {
        return Carbon::parse($this->data['dateTimePub']);
    }

    public function getImageUrl(): ?string
    {
        return $this->data['image'];
    }

    public function getLanguage(): ?string
    {
        return $this->data['lang'];
    }

    public function getDesciption(): ?string
    {
        return $this->data['body'];
    }

    public function getProviderID(): string
    {
        return $this->data['uri'];
    }

    public function getSourceKey(): string
    {
        return $this->data['source']['uri'];
    }

    public function getSourceName(): string
    {
        return $this->data['source']['title'];
    }

    public function getAuthorName(): ?string
    {
        return $this->data['authors'][0]['name'] ?? null;
    }
}
