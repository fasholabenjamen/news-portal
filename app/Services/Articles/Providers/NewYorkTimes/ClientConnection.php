<?php

namespace App\Services\Articles\Providers\NewYorkTimes;

use App\Contracts\Connectors\ConnectorContract;
use App\Helpers\ClientResponse;
use App\Traits\ClientConnectionHelper;
use Illuminate\Http\Client\PendingRequest;

class ClientConnection implements ConnectorContract
{
    use ClientConnectionHelper;

    const BASE_URL = 'https://api.nytimes.com/svc/mostpopular/v2/';
    protected string $api_token;
    protected string $auth_key = 'api-key';
    protected PendingRequest $client;

    public function __construct()
    {
        $this->api_token = config('services.new_york_times.api_token');
        $this->initRequest(static::BASE_URL);
    }

    public function getArticles(array $params = []): ClientResponse
    {
        return $this->sendGetRequest(uri: 'viewed/1.json', parameters: $params);
    }
}
