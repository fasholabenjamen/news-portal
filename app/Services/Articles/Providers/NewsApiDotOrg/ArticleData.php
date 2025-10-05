<?php

namespace App\Services\Articles\Providers\NewsApiDotOrg;

use App\Contracts\Article\{
    ArticleDataContract,
    HasAuthorName,
    HasDescription,
    HasImageUrl,
    HasSource,
};
use App\Enums\ArticleProviders;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ArticleData implements ArticleDataContract, HasImageUrl, HasDescription, HasAuthorName, HasSource
{
    public function __construct(protected array $data) {}

    public function getProviderKey(): string
    {
        return ArticleProviders::NEWS_API_DOT_ORG->value;
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
        return $this->data['url'];
    }

    public function getPublishedAt(): Carbon
    {
        return Carbon::parse($this->data['publishedAt']);
    }

    public function getImageUrl(): ?string
    {
        return $this->data['urlToImage'];
    }

    public function getDesciption(): ?string
    {
        return $this->data['description'];
    }

    public function getAuthorName(): ?string
    {
        return $this->data['author'];
    }

    public function getSourceKey(): string
    {
        return $this->data['source']['id'] ?? Str::slug($this->getSourceName());
    }

    public function getSourceName(): string
    {
        return $this->data['source']['name'] ?? 'Unknown';
    }
}
