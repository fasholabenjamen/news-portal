<?php

namespace App\Services\Articles\Providers\NewsApiDotOrg;

use App\Contracts\Connectors\NewsApiDotOrgConnector;
use App\Services\Articles\Providers\BaseProvider;
use App\Services\Articles\Providers\NewsApiDotOrg\ArticleData;
use Illuminate\Support\Collection;

class NewsApiDotOrgProvider extends BaseProvider
{
    public function __construct(protected NewsApiDotOrgConnector $client) {}

    public function fetchAndStoreArticles(): void
    {
        $sourcesReq = $this->client->getTopHeadlineSource(['language' => 'en']);
        if ($sourcesReq->failed()) {
            // Handle error as needed
            return;
        }

        collect($sourcesReq->data['sources'] ?? [])->each(function ($source) use (&$params) {
            $params = [
                'sources' => $source['id'],
                'language' => 'en'
            ];
            $articlesReq = $this->client->getArticles($params);
            if ($articlesReq->failed()) {
                // Handle error as needed
                return;
            }
            $articles = collect($articlesReq->data['articles'] ?? []);
            $this->processData($articles);
        });
    }

    protected function processData(Collection $data): void
    {
        $articles = $data->map(static fn(array $article) => new ArticleData($article));
        $this->saveArticles($articles);
    }
}
