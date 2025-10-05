<?php

namespace App\Services\Articles\Providers\NewsApiDotOrg;

use App\Contracts\Connectors\NewsApiDotOrgConnector;
use App\Helpers\ClientResponse;
use App\Traits\ClientConnectionHelper;
use Illuminate\Http\Client\PendingRequest;
class ClientConnection implements NewsApiDotOrgConnector
{
    use ClientConnectionHelper;

    const BASE_URL = 'https://newsapi.org/v2/';
    protected string $api_token;
    protected string $auth_key = 'apiKey';
    protected PendingRequest $client;

    public function __construct()
    {
        $this->api_token = config('services.news_api_dot_org.api_token');
        $this->initRequest(static::BASE_URL);
    }

    public function getArticles(array $params = []): ClientResponse
    {
        return $this->sendGetRequest(uri: 'everything', parameters: $params);
    }

    public function getTopHeadlineSource(array $params): ClientResponse
    {
        return $this->sendGetRequest(uri: 'top-headlines/sources', parameters: $params);
    }
}
