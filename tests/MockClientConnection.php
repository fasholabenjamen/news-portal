<?php

namespace Tests;

use App\Contracts\Connectors\ConnectorContract;
use App\Helpers\ClientResponse;
use Illuminate\Support\Facades\Http;

class MockClientConnection implements ConnectorContract
{
    protected $client;
    protected array $mockResponses = [];
    protected int $callCount = 0;

    public function __construct(array $mockResponses = [])
    {
        $this->mockResponses = $mockResponses;
        $this->client = Http::fake();
    }

    public function setMockResponse(int $statusCode, ?string $errorMsg = null, array $data = []): self
    {
        $this->mockResponses[] = new ClientResponse($statusCode, $errorMsg, $data);
        return $this;
    }

    public function setMockResponses(array $responses): self
    {
        $this->mockResponses = $responses;
        return $this;
    }

    public function getArticles(array $params = []): ClientResponse
    {
        $response = $this->getNextMockResponse();
        $this->callCount++;
        return $response;
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }

    public function resetCallCount(): void
    {
        $this->callCount = 0;
    }

    protected function getNextMockResponse(): ClientResponse
    {
        if (empty($this->mockResponses)) {
            return new ClientResponse(200, null, []);
        }

        if (count($this->mockResponses) === 1) {
            return $this->mockResponses[0];
        }

        return array_shift($this->mockResponses);
    }
}
