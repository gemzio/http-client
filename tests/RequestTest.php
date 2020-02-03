<?php

namespace Gemz\HttpClient\Tests;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Response;
use Gemz\HttpClient\Testing\MockClient;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function can_test()
    {
        $body = '{"id" : 1, "users" : [{"id" : 1}, {"id" : 2}]}';

        $client = new MockClient(Config::build()
            ->header('x-key', 'test')
            ->baseUri('http://test.com'));

        $response = $client
            ->mockBody($body)
            ->asMultipart()
            ->header('x-user', 1)
            //->header('content-type', 'application/x-www-form-urlencoded')
            ->get('users');
    }
}
