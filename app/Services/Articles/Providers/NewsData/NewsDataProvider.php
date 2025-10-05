<?php

namespace App\Services\Articles\Providers\NewsData;

use App\Contracts\Connectors\ConnectorContract;
use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewsData\ArticleData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewsDataProvider extends BaseProvider
{
    public function __construct(protected ConnectorContract $client) {}

    public function fetchAndStoreArticles(): void
    {
        $next_page = null;
        $params = [
            'language' => 'en'
        ];
        $maxPages = config('services.news_data.max_page');
        $pageCount = 0;

        do {
            $pageCount++;
            if ($pageCount > $maxPages) {
                break;
            }

            $articlesReq = $this->client->getArticles($params);

            if ($articlesReq->failed()) {
                Log::error('Request to get articles from NewsData failed' . $articlesReq->getErrorMessage());
                // Handle error as needed
                break;
            }

            $articles = collect($articlesReq->data['results'] ?? []);
            $this->processData($articles);
            $next_page = $articlesReq->data['nextPage'] ?? null;

            if ($next_page) {
                $params['page'] = $next_page;
            }
        } while ($next_page);
    }

    protected function processData(Collection $data): void
    {
        $articles = $data->map(static fn(array $article) => new ArticleData($article));
        $this->saveArticles($articles);
    }
}
