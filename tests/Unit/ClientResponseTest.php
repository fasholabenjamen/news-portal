<?php

namespace Tests\Unit;

use App\Helpers\ClientResponse;
use PHPUnit\Framework\TestCase;

class ClientResponseTest extends TestCase
{
    public function test_is_successful_returns_true_for_2xx_status_codes(): void
    {
        $response = new ClientResponse(200, null, ['data' => 'test']);
        $this->assertTrue($response->isSuccessful());

        $response = new ClientResponse(201, null, ['data' => 'test']);
        $this->assertTrue($response->isSuccessful());

        $response = new ClientResponse(299, null, ['data' => 'test']);
        $this->assertTrue($response->isSuccessful());
    }

    public function test_is_successful_returns_false_for_non_2xx_status_codes(): void
    {
        $response = new ClientResponse(199);
        $this->assertFalse($response->isSuccessful());

        $response = new ClientResponse(300);
        $this->assertFalse($response->isSuccessful());

        $response = new ClientResponse(404, 'Not Found');
        $this->assertFalse($response->isSuccessful());

        $response = new ClientResponse(500, 'Server Error');
        $this->assertFalse($response->isSuccessful());
    }

    public function test_failed_returns_true_for_failed_requests(): void
    {
        $response = new ClientResponse(404, 'Not Found');
        $this->assertTrue($response->failed());

        $response = new ClientResponse(500, 'Server Error');
        $this->assertTrue($response->failed());
    }

    public function test_failed_returns_false_for_successful_requests(): void
    {
        $response = new ClientResponse(200, null, ['data' => 'test']);
        $this->assertFalse($response->failed());
    }

    public function test_get_error_message_returns_error_message(): void
    {
        $errorMessage = 'Connection timeout';
        $response = new ClientResponse(503, $errorMessage);
        
        $this->assertEquals($errorMessage, $response->getErrorMessage());
    }

    public function test_get_error_message_returns_null_when_no_error(): void
    {
        $response = new ClientResponse(200, null, ['data' => 'test']);
        
        $this->assertNull($response->getErrorMessage());
    }

    public function test_set_error_message_updates_error_message(): void
    {
        $response = new ClientResponse(200);
        $this->assertNull($response->getErrorMessage());

        $errorMessage = 'New error message';
        $response->setErrorMessage($errorMessage);
        
        $this->assertEquals($errorMessage, $response->getErrorMessage());
    }

    public function test_constructor_sets_properties_correctly(): void
    {
        $statusCode = 200;
        $errorMsg = null;
        $data = ['key' => 'value', 'nested' => ['data' => 'test']];

        $response = new ClientResponse($statusCode, $errorMsg, $data);

        $this->assertEquals($statusCode, $response->status_code);
        $this->assertEquals($errorMsg, $response->error_msg);
        $this->assertEquals($data, $response->data);
    }

    public function test_constructor_with_only_status_code(): void
    {
        $response = new ClientResponse(404);

        $this->assertEquals(404, $response->status_code);
        $this->assertNull($response->error_msg);
        $this->assertEquals([], $response->data);
    }
}
