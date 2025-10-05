<?php

namespace App\Services\Articles;

use App\Enums\ArticleProviders;
use App\Services\Articles\Providers\BaseProvider;

class ArticleProviderFactory
{
    public static function getProviders(): array
    {
        return ArticleProviders::cases();
    }

    public static function getProvider(string $provider_key): ?BaseProvider
    {
        return ArticleProviders::tryFrom($provider_key)?->getInstance();
    }
}
