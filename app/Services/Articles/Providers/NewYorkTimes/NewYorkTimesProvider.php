<?php

namespace App\Services\Articles\Providers\NewYorkTimes;

use App\Contracts\Connectors\ConnectorContract;
use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewYorkTimes\ArticleData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NewYorkTimesProvider extends BaseProvider
{
    public function __construct(protected ConnectorContract $client) {}

    public function fetchAndStoreArticles(): void
    {
        $articlesReq = $this->client->getArticles();

        if ($articlesReq->failed()) {
            Log::error('Request to get articles from New York Times failed' . $articlesReq->getErrorMessage());
            // Handle error as needed
            return;
        }

        $articles = collect($articlesReq->data['results'] ?? []);
        $this->processData($articles);
    }

    protected function processData(Collection $data): void
    {
        $articles = $data->map(static fn(array $article) => new ArticleData($article));
        $this->saveArticles($articles);
    }
}
