<?php

namespace App\Services\Articles\Providers\NewsData;

use App\Contracts\Connectors\ConnectorContract;
use App\Helpers\ClientResponse;
use App\Traits\ClientConnectionHelper;
use Illuminate\Http\Client\PendingRequest;

class ClientConnection implements ConnectorContract
{
    use ClientConnectionHelper;

    const BASE_URL = 'https://newsdata.io/api/1/';
    protected string $api_token;
    protected PendingRequest $client;

    public function __construct()
    {
        $this->api_token = config('services.news_data.api_token');
        $this->initRequest(static::BASE_URL);
    }

    public function getArticles(array $params): ClientResponse
    {
        return $this->sendGetRequest(uri: 'latest', parameters: $params);
    }
}
