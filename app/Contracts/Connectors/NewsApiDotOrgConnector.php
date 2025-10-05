<?php

namespace App\Contracts\Connectors;

use App\Contracts\Connectors\ConnectorContract;
use App\Helpers\ClientResponse;

interface NewsApiDotOrgConnector extends ConnectorContract {
    public function getTopHeadlineSource(array $params): ClientResponse;
}
