<?php

namespace Tests\Unit;

use App\Helpers\ClientResponse;
use App\Traits\ClientConnectionHelper;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientConnectionHelperTraitTest extends TestCase
{
    private $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = new class {
            use ClientConnectionHelper;

            public $auth_key = 'apiKey';
            public $api_token = 'test-token-123';

            public function testSendGetRequest(string $uri, array $parameters = []): ClientResponse
            {
                $this->initRequest('https://api.example.com/');
                return $this->sendGetRequest($uri, $parameters);
            }

            public function testInitRequest(string $base_url): void
            {
                $this->initRequest($base_url);
            }
        };
    }

    public function test_send_get_request_returns_successful_response(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response([
                'data' => 'test data',
                'status' => 'success'
            ], 200)
        ]);

        $response = $this->trait->testSendGetRequest('test-endpoint', ['param1' => 'value1']);

        $this->assertInstanceOf(ClientResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(200, $response->status_code);
        $this->assertNull($response->error_msg);
        $this->assertArrayHasKey('data', $response->data);
        $this->assertEquals('test data', $response->data['data']);
    }

    public function test_send_get_request_includes_auth_parameters(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response(['success' => true], 200)
        ]);

        $this->trait->testSendGetRequest('endpoint', ['custom' => 'param']);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.example.com/endpoint?custom=param&apiKey=test-token-123';
        });
    }

    public function test_send_get_request_handles_404_error(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response('Not Found', 404)
        ]);

        $response = $this->trait->testSendGetRequest('not-found');

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->failed());
        $this->assertEquals(404, $response->status_code);
        $this->assertEquals('Not Found', $response->error_msg);
        $this->assertEquals([], $response->data);
    }

    public function test_send_get_request_handles_500_error(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response('Internal Server Error', 500)
        ]);

        $response = $this->trait->testSendGetRequest('error-endpoint');

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->failed());
        $this->assertEquals(500, $response->status_code);
        $this->assertEquals('Internal Server Error', $response->error_msg);
    }

    public function test_send_get_request_handles_connection_exception(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });

        $response = $this->trait->testSendGetRequest('timeout-endpoint');

        $this->assertTrue($response->failed());
        $this->assertEquals(503, $response->status_code);
        $this->assertEquals('Connection timeout', $response->error_msg);
        $this->assertEquals([], $response->data);
    }

    public function test_send_get_request_with_empty_parameters(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response(['result' => 'ok'], 200)
        ]);

        $response = $this->trait->testSendGetRequest('endpoint');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.example.com/endpoint?apiKey=test-token-123';
        });

        $this->assertTrue($response->isSuccessful());
    }

    public function test_send_get_request_with_multiple_parameters(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response(['result' => 'ok'], 200)
        ]);

        $params = [
            'page' => 1,
            'limit' => 10,
            'sort' => 'date'
        ];

        $response = $this->trait->testSendGetRequest('articles', $params);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'page=1')
                && str_contains($request->url(), 'limit=10')
                && str_contains($request->url(), 'sort=date')
                && str_contains($request->url(), 'apiKey=test-token-123');
        });

        $this->assertTrue($response->isSuccessful());
    }

    public function test_init_request_sets_base_url(): void
    {
        Http::fake([
            'different-api.com/*' => Http::response(['test' => 'data'], 200)
        ]);

        $trait = new class {
            use ClientConnectionHelper;

            public $auth_key = 'key';
            public $api_token = 'token';

            public function testWithDifferentBaseUrl(): ClientResponse
            {
                $this->initRequest('https://different-api.com/v1/');
                return $this->sendGetRequest('endpoint');
            }
        };

        $response = $trait->testWithDifferentBaseUrl();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'different-api.com/v1/endpoint');
        });

        $this->assertTrue($response->isSuccessful());
    }

    public function test_send_get_request_handles_json_response(): void
    {
        $jsonData = [
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2']
            ],
            'total' => 2
        ];

        Http::fake([
            'api.example.com/*' => Http::response($jsonData, 200)
        ]);

        $response = $this->trait->testSendGetRequest('items');

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($jsonData, $response->data);
        $this->assertCount(2, $response->data['items']);
    }

    public function test_send_get_request_handles_201_created_status(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response(['created' => true], 201)
        ]);

        $response = $this->trait->testSendGetRequest('create');

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(201, $response->status_code);
    }

    public function test_send_get_request_handles_401_unauthorized(): void
    {
        Http::fake([
            'api.example.com/*' => Http::response('Unauthorized', 401)
        ]);

        $response = $this->trait->testSendGetRequest('protected');

        $this->assertTrue($response->failed());
        $this->assertEquals(401, $response->status_code);
    }
}
