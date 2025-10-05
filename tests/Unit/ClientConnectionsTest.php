<?php

namespace Tests\Unit;

use App\Contracts\Connectors\ConnectorContract;
use App\Contracts\Connectors\NewsApiDotOrgConnector;
use App\Helpers\ClientResponse;
use App\Services\Articles\Providers\NewsApiDotAi\ClientConnection as NewsApiDotAiConnection;
use App\Services\Articles\Providers\NewsApiDotOrg\ClientConnection as NewsApiDotOrgConnection;
use App\Services\Articles\Providers\NewsData\ClientConnection as NewsDataConnection;
use App\Services\Articles\Providers\NewYorkTimes\ClientConnection as NewYorkTimesConnection;
use App\Traits\ClientConnectionHelper;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientConnectionsTest extends TestCase
{
    public function test_news_api_dot_org_connection_implements_connector_contract(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['status' => 'ok'], 200)
        ]);

        $connection = new NewsApiDotOrgConnection();
        
        $this->assertInstanceOf(ConnectorContract::class, $connection);
        $this->assertInstanceOf(NewsApiDotOrgConnector::class, $connection);
    }

    public function test_news_api_dot_org_connection_uses_client_connection_helper(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['status' => 'ok'], 200)
        ]);

        $reflection = new \ReflectionClass(NewsApiDotOrgConnection::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains(ClientConnectionHelper::class, $traits);
    }

    public function test_news_api_dot_org_connection_has_correct_base_url(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []], 200)
        ]);

        config(['services.news_api_dot_org.api_token' => 'test-token']);
        
        $connection = new NewsApiDotOrgConnection();
        $connection->getArticles();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'newsapi.org/v2/');
        });
    }

    public function test_news_api_dot_org_connection_get_articles_returns_client_response(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []], 200)
        ]);

        config(['services.news_api_dot_org.api_token' => 'test-token']);
        
        $connection = new NewsApiDotOrgConnection();
        $response = $connection->getArticles(['language' => 'en']);

        $this->assertInstanceOf(ClientResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_news_api_dot_org_connection_get_top_headline_source(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['sources' => []], 200)
        ]);

        config(['services.news_api_dot_org.api_token' => 'test-token']);
        
        $connection = new NewsApiDotOrgConnection();
        $response = $connection->getTopHeadlineSource(['language' => 'en']);

        $this->assertInstanceOf(ClientResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'top-headlines/sources');
        });
    }

    public function test_news_api_dot_ai_connection_implements_connector_contract(): void
    {
        Http::fake([
            'eventregistry.org/*' => Http::response(['articles' => []], 200)
        ]);

        $connection = new NewsApiDotAiConnection();
        
        $this->assertInstanceOf(ConnectorContract::class, $connection);
    }

    public function test_news_api_dot_ai_connection_has_correct_base_url(): void
    {
        Http::fake([
            'eventregistry.org/*' => Http::response(['articles' => []], 200)
        ]);

        config(['services.news_api_dot_ai.api_token' => 'test-token']);
        
        $connection = new NewsApiDotAiConnection();
        $connection->getArticles();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'eventregistry.org/api/v1/');
        });
    }

    public function test_news_api_dot_ai_connection_get_articles_uses_correct_endpoint(): void
    {
        Http::fake([
            'eventregistry.org/*' => Http::response(['articles' => ['results' => []]], 200)
        ]);

        config(['services.news_api_dot_ai.api_token' => 'test-token']);
        
        $connection = new NewsApiDotAiConnection();
        $connection->getArticles(['lang' => 'eng']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'article/getArticles');
        });
    }

    public function test_new_york_times_connection_implements_connector_contract(): void
    {
        Http::fake([
            'nytimes.com/*' => Http::response(['results' => []], 200)
        ]);

        $connection = new NewYorkTimesConnection();
        
        $this->assertInstanceOf(ConnectorContract::class, $connection);
    }

    public function test_new_york_times_connection_has_correct_base_url(): void
    {
        Http::fake([
            'nytimes.com/*' => Http::response(['results' => []], 200)
        ]);

        config(['services.new_york_times.api_token' => 'test-token']);
        
        $connection = new NewYorkTimesConnection();
        $connection->getArticles();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'api.nytimes.com/svc/mostpopular/v2/');
        });
    }

    public function test_new_york_times_connection_get_articles_uses_correct_endpoint(): void
    {
        Http::fake([
            'nytimes.com/*' => Http::response(['results' => []], 200)
        ]);

        config(['services.new_york_times.api_token' => 'test-token']);
        
        $connection = new NewYorkTimesConnection();
        $connection->getArticles();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'viewed/1.json');
        });
    }

    public function test_news_data_connection_implements_connector_contract(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response(['results' => []], 200)
        ]);

        $connection = new NewsDataConnection();
        
        $this->assertInstanceOf(ConnectorContract::class, $connection);
    }

    public function test_news_data_connection_has_correct_base_url(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response(['results' => []], 200)
        ]);

        config(['services.news_data.api_token' => 'test-token']);
        
        $connection = new NewsDataConnection();
        $connection->getArticles();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'newsdata.io/api/1/');
        });
    }

    public function test_news_data_connection_get_articles_uses_correct_endpoint(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response(['results' => []], 200)
        ]);

        config(['services.news_data.api_token' => 'test-token']);
        
        $connection = new NewsDataConnection();
        $connection->getArticles(['language' => 'en']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'latest');
        });
    }

    public function test_all_connections_handle_failed_responses(): void
    {
        $connections = [
            ['class' => NewsApiDotOrgConnection::class, 'url' => 'newsapi.org/*', 'config' => 'services.news_api_dot_org.api_token'],
            ['class' => NewsApiDotAiConnection::class, 'url' => 'eventregistry.org/*', 'config' => 'services.news_api_dot_ai.api_token'],
            ['class' => NewYorkTimesConnection::class, 'url' => 'nytimes.com/*', 'config' => 'services.new_york_times.api_token'],
            ['class' => NewsDataConnection::class, 'url' => 'newsdata.io/*', 'config' => 'services.news_data.api_token'],
        ];

        foreach ($connections as $connectionInfo) {
            Http::fake([
                $connectionInfo['url'] => Http::response('Server Error', 500)
            ]);

            config([$connectionInfo['config'] => 'test-token']);
            
            $connection = new $connectionInfo['class']();
            $response = $connection->getArticles();

            $this->assertInstanceOf(ClientResponse::class, $response);
            $this->assertTrue($response->failed());
            $this->assertEquals(500, $response->status_code);
        }
    }

    public function test_all_connections_include_auth_key_in_requests(): void
    {
        $connections = [
            [
                'class' => NewsApiDotOrgConnection::class, 
                'url' => 'newsapi.org/*', 
                'config' => 'services.news_api_dot_org.api_token',
                'auth_key' => 'apiKey'
            ],
            [
                'class' => NewsApiDotAiConnection::class, 
                'url' => 'eventregistry.org/*', 
                'config' => 'services.news_api_dot_ai.api_token',
                'auth_key' => 'apiKey'
            ],
            [
                'class' => NewYorkTimesConnection::class, 
                'url' => 'nytimes.com/*', 
                'config' => 'services.new_york_times.api_token',
                'auth_key' => 'api-key'
            ],
            [
                'class' => NewsDataConnection::class, 
                'url' => 'newsdata.io/*', 
                'config' => 'services.news_data.api_token',
                'auth_key' => 'apiKey'
            ],
        ];

        foreach ($connections as $connectionInfo) {
            Http::fake([
                $connectionInfo['url'] => Http::response(['data' => 'test'], 200)
            ]);

            config([$connectionInfo['config'] => 'test-token-123']);
            
            $connection = new $connectionInfo['class']();
            $connection->getArticles();

            $authKey = $connectionInfo['auth_key'];
            
            Http::assertSent(function (Request $request) use ($authKey) {
                return str_contains($request->url(), $authKey . '=test-token-123');
            });
        }
    }
}
