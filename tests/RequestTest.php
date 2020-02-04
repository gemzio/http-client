<?php

namespace Gemz\HttpClient\Tests;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Testing\MockClient;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function can_add_query_params_to_request()
    {
        $client = new MockClient(Config::build()->baseUri('http://myapi.com'));

        $client
            ->queryParam('id', 1)
            ->queryParams(['filter' => 'test', 'perPage' => 10]);

        $this->assertArrayHasKey('query', $client->getRequestOptions());
        $this->assertEquals(
            $client->getRequestOptions()['query'],
            ['id' => 1, 'filter' => 'test', 'perPage' => 10]
        );
    }

    /** @test */
    public function can_add_headers_to_request()
    {
        $client = new MockClient(Config::build()
            ->header('key', '1')
            ->baseUri('http://myapi.com'));

        $client
            ->header('key', '2')
            ->headers(['filter' => 'test', 'page' => 10]);

        $this->assertArrayHasKey('headers', $client->getRequestOptions());
        $this->assertSame(
            $client->getRequestOptions()['headers'],
            ['content-type' => 'application/json', 'key' => '2', 'filter' => 'test', 'page' => '10']
        );
    }

    /** @test */
    public function can_send_body_as_string()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->asPlainText()
            ->payload('test');

        $this->assertSame(
            $client->getRequestOptions()['body'],
            'test'
        );

        $this->assertSame(
            $client->getRequestOptions()['headers']['content-type'],
            'text/plain'
        );
    }

    /** @test */
    public function can_send_request_as_json()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->asJson()
            ->payload(['key' => 'test']);

        $this->assertSame(
            $client->getRequestOptions()['json'],
            ['key' => 'test']
        );

        $this->assertSame(
            $client->getRequestOptions()['headers']['content-type'],
            'application/json'
        );
    }

    /** @test */
    public function can_send_request_as_multipart()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client->asMultipart();

        $this->assertSame(
            $client->getRequestOptions()['headers']['content-type'],
            'multipart/form-data'
        );
    }

    /** @test */
    public function can_send_request_as_form_params()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->asFormParams()
            ->payload(['filter' => 'test', 'page' => '10']);

        $this->assertSame(
            $client->getRequestOptions()['body'],
            ['filter' => 'test', 'page' => '10']
        );
    }

    /** @test */
    public function can_authenticate_with_basic()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->authBasic('username', 'password');

        $this->assertSame(
            $client->getRequestOptions()['auth_basic'],
            ['username', 'password']
        );

        $client
            ->authBasic('username');

        $this->assertSame(
            $client->getRequestOptions()['auth_basic'],
            ['username']
        );
    }

    /** @test */
    public function can_authenticate_with_bearer_token()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->authBearer('token');

        $this->assertSame(
            $client->getRequestOptions()['auth_bearer'],
            'token'
        );
    }

    /** @test */
    public function can_change_baseuri_with_request()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client
            ->baseUri('http://myapi2.com');

        $this->assertSame(
            $client->getRequestOptions()['base_uri'],
            'http://myapi2.com'
        );
    }

    /** @test */
    public function can_add_accept_header()
    {
        $client = new MockClient(Config::build()
            ->accept('application/json')
            ->baseUri('http://myapi.com'));

        $this->assertSame(
            $client->getRequestOptions()['headers']['accept'],
            'application/json'
        );
    }

    /** @test */
    public function can_add_custom_data_with_different_types()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        // plain
        $client->customData('test');

        $this->assertSame(
            $client->getRequestOptions()['user_data'],
            'test'
        );

        // array
        $client->customData(['key' => 'value']);

        $this->assertSame(
            $client->getRequestOptions()['user_data'],
            ['key' => 'value']
        );

        // object
        $client->customData(Client::create());
        $this->assertInstanceOf(Client::class, $client->getRequestOptions()['user_data']);
    }


    /** @test */
    public function can_add_max_duration()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client->maxDuration(20);

        $this->assertSame(
            $client->getRequestOptions()['max_duration'],
            20
        );
    }

    /** @test */
    public function can_add_max_redirects()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client->maxRedirects(20);

        $this->assertSame(
            $client->getRequestOptions()['max_redirects'],
            20
        );
    }

    /** @test */
    public function can_add_timeout()
    {
        $client = new MockClient(Config::build()
            ->baseUri('http://myapi.com'));

        $client->timeout(20);

        $this->assertSame(
            $client->getRequestOptions()['timeout'],
            20.0
        );
    }
}
