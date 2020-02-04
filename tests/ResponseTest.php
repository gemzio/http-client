<?php

namespace Gemz\HttpClient\Tests;

use Gemz\HttpClient\Config;
use Gemz\HttpClient\Response;
use Gemz\HttpClient\Testing\MockClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class ResponseTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {

    }

    /** @test */
    public function response_is_valid_with_status_code()
    {
        $status = [200, 201, 202, 204, 290, 299];

        foreach ($status as $value) {
            $response = $this->mockResponse('', ['http_code' => $value]);
            $response = new Response($response);
            $this->assertTrue($response->isSuccess());
            $this->assertFalse($response->isServerError());
            $this->assertFalse($response->isClientError());
            $this->assertFalse($response->isRedirect());
        }
    }

    /** @test */
    public function response_is_redirect()
    {
        $status = [300, 301, 302];

        foreach ($status as $value) {
            $response = $this->mockResponse('', ['http_code' => $value]);
            $response = new Response($response);
            $this->assertTrue($response->isRedirect());
            $this->assertFalse($response->isServerError());
            $this->assertFalse($response->isClientError());
            $this->assertFalse($response->isSuccess());
        }
    }

    /** @test */
    public function response_is_client_error()
    {
        $status = [400, 401, 404, 499];

        foreach ($status as $value) {
            $response = $this->mockResponse('', ['http_code' => $value]);
            $response = new Response($response);
            $this->assertTrue($response->isClientError());
            $this->assertFalse($response->isSuccess());
            $this->assertFalse($response->isServerError());
            $this->assertFalse($response->isRedirect());
        }
    }

    /** @test */
    public function response_is_server_error()
    {
        $status = [500, 504, 599, 600, 700];

        foreach ($status as $value) {
            $response = $this->mockResponse('', ['http_code' => $value]);
            $response = new Response($response);
            $this->assertTrue($response->isServerError());
            $this->assertFalse($response->isClientError());
            $this->assertFalse($response->isSuccess());
            $this->assertFalse($response->isRedirect());
        }
    }

    /** @test */
    public function can_detect_headers()
    {
        $response = $this->mockResponse('', [
            'http_code' => 200,
            'response_headers' => ['g-client' => 'Yeah']
        ]);

        $response = new Response($response);

        $this->assertEquals($response->header('g-client'), 'Yeah');
        $this->assertEquals($response->headers()['g-client'], 'Yeah');
    }

    /** @test */
    public function can_get_body_as_string()
    {
        $body = 'this is a test';
        $info = ['http_code' => 200];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        $response = $client
            ->mockInfo($info)
            ->mockBody($body)
            ->get('tests');

        $this->assertEquals($response->body(), 'this is a test');
        $this->assertEquals($response->asString(), 'this is a test');
    }

    /** @test */
    public function can_get_body_as_object()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';
        $info = ['http_code' => 200];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->get('tests');

        $this->assertTrue(is_object($response->asObject()));
    }

    /** @test */
    public function can_get_body_as_array()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';
        $info = ['http_code' => 200];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->get('tests');

        $this->assertTrue(is_array($response->asArray()));
    }

    /** @test */
    public function can_get_body_as_collection()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';
        $info = ['http_code' => 200];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->get('tests');

        $this->assertTrue(is_array($response->asCollection()->toArray()));
    }

    /** @test */
    public function can_check_contenttype()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';
        $info = ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->header('content-type', 'application/json')
            ->get('tests');

        $this->assertTrue($response->isJson() == 'application/json');
    }

    /** @test */
    public function can_transport_custom_data()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';
        $info = ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']];

        $client = new MockClient(Config::build()->baseUri('http://localhost.test'));

        // string
        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->customData('test')
            ->get('tests');

        $this->assertTrue($response->customData() == 'test');

        // array
        $response = $client
            ->mockBody($body)
            ->mockInfo($info)
            ->customData(['test'])
            ->get('tests');

        $this->assertTrue($response->customData() == ['test']);
    }

    /**
     * @param string $body
     * @param array $info
     *
     * @return MockResponse
     */
    protected function mockResponse(string $body, array $info)
    {
        return new MockResponse($body, $info);
    }
}
