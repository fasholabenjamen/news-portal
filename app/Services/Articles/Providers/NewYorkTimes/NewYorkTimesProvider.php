<?php

namespace App\Services\Articles\Providers\NewYorkTimes;

use App\Contracts\Connectors\ConnectorContract;
use App\Services\Articles\Providers\BaseProvider;
use Illuminate\Support\Collection;
use App\Services\Articles\Providers\NewYorkTimes\ArticleData;

class NewYorkTimesProvider extends BaseProvider
{
    public function __construct(protected ConnectorContract $client) {}

    public function fetchAndStoreArticles(): void
    {
        $articlesReq = $this->client->getArticles();

        if ($articlesReq->failed()) {
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
