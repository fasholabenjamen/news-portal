<?php

namespace App\Services\Articles\Providers\NewsApiDotAi;

use App\Contracts\Connectors\ConnectorContract;
use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewsApiDotAi\ArticleData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsApiDotAiProvider extends BaseProvider
{
    public function __construct(protected ConnectorContract $client) {}

    public function fetchAndStoreArticles(): void
    {
        $next_page = 1;
        $params = [
            'lang' => 'eng'
        ];
        $maxPages = $totalPages = config('services.news_api_dot_ai.max_page');

        while ($next_page < $maxPages && $next_page <= $totalPages) {
            $params['page'] = $next_page;
            $articlesReq = $this->client->getArticles($params);
            if ($articlesReq->failed()) {
                Log::error('Request to get articles from NewsApi.ai failed' . $articlesReq->getErrorMessage());
                // Handle error as needed
                break;
            }

            $articles = collect($articlesReq->data['articles']['results'] ?? []);
            $this->processData($articles);
            $totalPages = $articlesReq->data['articles']['pages'] ?? 0;
            $next_page++;
        }
    }

    protected function processData(Collection $data): void
    {
        $articles = $data->map(static fn(array $article) => new ArticleData($article));
        $this->saveArticles($articles);
    }
}
