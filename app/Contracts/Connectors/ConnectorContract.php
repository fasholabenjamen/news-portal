<?php

namespace App\Contracts\Connectors;

use App\Helpers\ClientResponse;

interface ConnectorContract {
    public function getArticles(array $params): ClientResponse;
}
