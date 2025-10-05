<?php

namespace App\Traits;

use App\Helpers\ClientResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

trait ClientConnectionHelper
{
    protected function sendGetRequest(string $uri, $parameters = []): ClientResponse
    {
        try {
            $res = $this->client->get($uri, array_merge($parameters, ['apiKey' => $this->api_token]));
        } catch (ConnectionException $exception) {
            return new ClientResponse(Response::HTTP_SERVICE_UNAVAILABLE, $exception->getMessage());
        }

        if ($res->failed()) {
            return new ClientResponse($res->status(), $res->body());
        }

        return new ClientResponse($res->status(), null, $res->json());
    }

    protected function initRequest(string $base_url): void
    {
        $this->client = Http::baseUrl($base_url);
    }
}