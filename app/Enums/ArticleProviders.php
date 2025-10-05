<?php

namespace App\Enums;

use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewsApiDotAi\NewsApiDotAiProvider;
use App\Services\Articles\Providers\NewsApiDotOrg\NewsApiDotOrgProvider;
use App\Services\Articles\Providers\NewsData\NewsDataProvider;
use App\Services\Articles\Providers\NewYorkTimes\NewYorkTimesProvider;

enum ArticleProviders: string {
    case NEWS_API_DOT_ORG = 'news_api_dot_org';
    case NEWS_API_DOT_AI = 'news_api_dot_ai';
    case NEWS_DATA = 'news_data';
    case NEW_YORK_TIMES = 'new_york_times';

    public function label(): string
    {
        return match ($this) {
            self::NEWS_API_DOT_ORG => 'News API (newsapi.org)',
            self::NEWS_API_DOT_AI => 'News API (newsapi.ai)',
            self::NEWS_DATA => 'News Data (newsdata.io)',
            self::NEW_YORK_TIMES => 'New York Times (nytimes.com)',
        };
    }

    public function getInstance(): BaseProvider
    {
        return match ($this) {
            self::NEWS_API_DOT_ORG => app(NewsApiDotOrgProvider::class),
            self::NEWS_API_DOT_AI => app(NewsApiDotAiProvider::class),
            self::NEWS_DATA => app(NewsDataProvider::class),
            self::NEW_YORK_TIMES => app(NewYorkTimesProvider::class),
        };
    }
}
