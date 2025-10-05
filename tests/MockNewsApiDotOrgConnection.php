<?php

namespace Tests;

use App\Contracts\Connectors\NewsApiDotOrgConnector;
use App\Helpers\ClientResponse;

class MockNewsApiDotOrgConnection extends MockClientConnection implements NewsApiDotOrgConnector
{
    protected array $topHeadlineMockResponses = [];
    protected int $topHeadlineCallCount = 0;

    public function setTopHeadlineMockResponse(int $statusCode, ?string $errorMsg = null, array $data = []): self
    {
        $this->topHeadlineMockResponses[] = new ClientResponse($statusCode, $errorMsg, $data);
        return $this;
    }

    public function setTopHeadlineMockResponses(array $responses): self
    {
        $this->topHeadlineMockResponses = $responses;
        return $this;
    }

    public function getTopHeadlineSource(array $params): ClientResponse
    {
        $response = $this->getNextTopHeadlineMockResponse();
        $this->topHeadlineCallCount++;
        return $response;
    }

    public function getTopHeadlineCallCount(): int
    {
        return $this->topHeadlineCallCount;
    }

    protected function getNextTopHeadlineMockResponse(): ClientResponse
    {
        if (empty($this->topHeadlineMockResponses)) {
            return new ClientResponse(200, null, ['sources' => []]);
        }

        if (count($this->topHeadlineMockResponses) === 1) {
            return $this->topHeadlineMockResponses[0];
        }

        return array_shift($this->topHeadlineMockResponses);
    }
}
