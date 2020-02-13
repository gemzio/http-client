<?php

namespace Gemz\HttpClient\Tests;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Response;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Stream;
use Gemz\HttpClient\Testing\MockClient;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;


class RequestTest extends TestCase
{
    protected $timer = [];
    protected $urls = [
        'https://api.leadmachine.dk/v1/users/?api_key=wpBV7vbzPOLduuAHfAdzT4mzxVVWySgElfFdH4Ec&access_token=QnM1NGdnM0JObW55WDl0Uk9kWW9adG04eTJiVUNUY2dCVDdVWWVZSA==%22',
        'https://api.leadmachine.dk/v1/users/97fb4e7f38b75907bd4a0aaef7387d5f?api_key=wpBV7vbzPOLduuAHfAdzT4mzxVVWySgElfFdH4Ec&access_token=QnM1NGdnM0JObW55WDl0Uk9kWW9adG04eTJiVUNUY2dCVDdVWWVZSA==%22',
        'https://api.leadmachine.dk/v1'
    ];

    protected $imgUrl = "https://http2.akamai.com/demo/tile-%s.png";

    /** @test */
    public function can_do_get_requests()
    {
        $response = Client::create()->get(sprintf($this->imgUrl, 8));

        $this->assertEquals($response->status(), 200);
    }

    /** @test */
    public function can_do_async_requests()
    {
        $client = Client::create();

        $responses = [];

        for ($i = 0; $i < 1; ++$i) {
            $responses[] = $client
                ->customData('req - ' . $i)
                ->get(sprintf($this->imgUrl, $i));
        }

         foreach ($responses as $response) {
             $this->assertEquals(200, $response->status());
         }

        $responses = [];

        $responses['r2'] = $client->get(sprintf($this->imgUrl, 2));
        $responses['r3'] = $client->get(sprintf($this->imgUrl, 3));
        $responses['r4'] = $client->get(sprintf($this->imgUrl, 4));

        foreach ($responses as $key => $response) {
            $this->assertEquals(200, $response->status());
            $this->assertTrue(Str::contains($key, 'r'));
        }

    }

    /** @test */
    public function can_do_stream_async_requests()
    {
        $client = Client::create();

        $responses = [];

        for ($i = 0; $i < 5; ++$i) {
            $responses[] = $client
                ->customData('req - ' . $i)
                ->get(sprintf($this->imgUrl, $i));
        }

        foreach ($client->stream($responses) as $response => $chunk) {

            Stream::from($response, $chunk)
                ->then(function (Response $response) {
                    $this->assertEquals(200, $response->status());
                    $this->assertTrue(Str::contains($response->customData(), 'req - '));
                })
                ->timeout(function ($response) {})
                ->catch(function ($exception) {});
        }
    }

    protected function timer($text)
    {
        $times = count($this->timer);

        if ($times == 0) {
            $this->timer[$text] = round(microtime(true), 4);
            return;
        }

        $lastTime = end($this->timer);

        $this->timer[$text] = round(microtime(true),4) - $lastTime;
        return;
    }

    /** @test */
    public function can_add_query_params_to_request()
    {
        $client = new MockClient(Config::make()->baseUri('http://myapi.com'));

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
        $client = new MockClient(Config::make()
            ->header('key', '1')
            ->baseUri('http://myapi.com'));

        $client
            ->header('key', '2')
            ->headers(['filter' => 'test', 'page' => 10]);

        $this->assertArrayHasKey('headers', $client->getRequestOptions());
        $this->assertSame(
            $client->getRequestOptions()['headers'],
            ['Content-Type' => 'application/json', 'key' => '2', 'filter' => 'test', 'page' => '10']
        );
    }

    /** @test */
    public function can_send_body_as_string()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client
            ->asPlainText()
            ->payload('test');

        $this->assertSame(
            $client->getRequestOptions()['headers']['Content-Type'],
            'text/plain'
        );
    }

    /** @test */
    public function can_send_request_as_json()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client
            ->asJson()
            ->payload(['key' => 'test']);

        $this->assertSame(
            $client->getRequestOptions()['headers']['Content-Type'],
            'application/json'
        );
    }

    /** @test */
    public function can_send_request_as_multipart()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client->asMultipart();

        $this->assertSame(
            $client->getRequestOptions()['headers']['Content-Type'],
            'multipart/form-data'
        );
    }

    /** @test */
    public function can_send_request_as_form_params()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client
            ->asFormParams()
            ->payload(['filter' => 'test', 'page' => '10']);

        $this->assertSame(
            $client->getRequestOptions()['headers']['Content-Type'],
            'application/x-www-form-urlencoded'
        );
    }

    /** @test */
    public function can_authenticate_with_basic()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client
            ->authBasic('username', 'password');

        $this->assertSame(
            $client->getRequestOptions()['auth_basic'],
            'username:password'
        );

        $client->authBasic('username');

        $this->assertSame(
            $client->getRequestOptions()['auth_basic'],
            'username'
        );
    }

    /** @test */
    public function can_authenticate_with_bearer_token()
    {
        $client = new MockClient(Config::make()
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
        $client = new MockClient(Config::make()
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
        $client = new MockClient(Config::make()
            ->accept('application/json')
            ->baseUri('http://myapi.com'));

        $this->assertSame(
            $client->getRequestOptions()['headers']['Accept'],
            'application/json'
        );
    }

    /** @test */
    public function can_add_custom_data_with_different_types()
    {
        $client = new MockClient(Config::make()
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
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client->maxDuration(20);

        $this->assertSame(
            $client->getRequestOptions()['max_duration'],
            20.0
        );
    }

    /** @test */
    public function can_add_max_redirects()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client->allowRedirects(20);

        $this->assertSame(
            $client->getRequestOptions()['max_redirects'],
            20
        );
    }

    /** @test */
    public function can_add_timeout()
    {
        $client = new MockClient(Config::make()
            ->baseUri('http://myapi.com'));

        $client->timeout(20);

        $this->assertSame(
            $client->getRequestOptions()['timeout'],
            20.0
        );
    }
}
