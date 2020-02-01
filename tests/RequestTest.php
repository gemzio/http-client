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
    public function test()
    {
        $cfg = Config::build()->baseUri('https://http2.akamai.com');
        $client = new Client($cfg);

        $responses = [];

        $start = microtime(true);
        for ($i = 1; $i < 350; $i++) {
            $responses[] = $client
                ->customData("item {$i}")
                ->get("demo/tile-{$i}.png");
        }
        $end = microtime(true);
        var_dump($end-$start);
        /** @var Response $response */
        foreach ($responses as $response) {
            $content = $response->body();
            var_dump($response->customData());
        }

        exit;
        $config = Config::build()
            ->header('x-instance', '9182371928371923789')
            ->header('x-project', '7xdUqZBo3h4a3XHR5RY6xVM0K5pEUj94')
            ->baseUri('https://api.streams.dev.p-bm.io');

        $client = Client::create($config);

        $response = $client
            ->payload(['filter' => ["where" => [['pbm.id', '=', '762890584fcb406aa51a403bfba14853']]]])
            ->post('v1/streams/events/accountuserlogs/search');

        if ($response->isClientError()) {
            dd($response->asObject());
        } else {
            dd($response->headers());
        }
    }

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

        dd(
            $response->headers(),
            $response->status(),
            $response->asArray(),
            $response->response()->getContent()
        );
    }
}
