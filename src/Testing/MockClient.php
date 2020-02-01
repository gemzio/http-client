<?php

namespace Gemz\HttpClient\Testing;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Response;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MockClient extends Client
{
    protected $mockResponse;
    protected $mockBody;
    protected $mockInfo = [];

    public function mockBody($body)
    {
        $this->mockBody = $body;

        return $this;
    }

    public function mockInfo(array $info)
    {
        $this->mockInfo = $info;

        return $this;
    }

    protected function buildMockResponse()
    {
        return new MockResponse(
            $this->mockBody,
            $this->buildMockInfo()
        );
    }

    protected function buildMockInfo()
    {
        $info = array_merge($this->getConfig(), $this->mockInfo);
        $info['response_headers'] = array_merge($this->getConfig()['headers'], $this->options['headers']);
        return $info;
    }

    protected function request(string $method, string $endpoint)
    {
        $client = new MockHttpClient($this->buildMockResponse(), $this->getBaseUri());

        return new Response(
            $client->request($method, $endpoint, $this->options)
        );
    }

    protected function getBaseUri(): string
    {
        $config = $this->config !== null
            ? $this->config->toArray()
            : Config::build()->toArray();

        return $config['base_uri'] ?: 'http://localhost.test';
    }

    protected function getConfig(): array
    {
        $config = $this->config !== null ? $this->config : Config::build();

        return $config->toArray();
    }
}
