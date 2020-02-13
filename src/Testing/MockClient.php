<?php

namespace Gemz\HttpClient\Testing;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Response;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MockClient extends Client
{
    /** @var MockResponse */
    protected $mockResponse;

    /** @var mixed */
    protected $mockBody;

    /** @var array<mixed> */
    protected $mockInfo = [];

    /**
     * @param mixed $body
     *
     * @return $this
     */
    public function mockBody($body): self
    {
        $this->mockBody = $body;

        return $this;
    }

    /**
     * @param array<mixed> $info
     *
     * @return $this
     */
    public function mockInfo(array $info): self
    {
        $this->mockInfo = $info;

        return $this;
    }

    /**
     * @return MockResponse
     */
    protected function buildMockResponse(): MockResponse
    {
        return new MockResponse(
            $this->mockBody,
            $this->buildMockInfo()
        );
    }

    /**
     * @return array<mixed>
     */
    protected function buildMockInfo(): array
    {
        $info = array_merge($this->getConfig(), $this->mockInfo);
        $info['response_headers'] = array_merge(
            $this->getConfig()['headers'] ?? [],
            $this->options['headers'] ?? []
        );

        return $info;
    }

    /**
     * @param string $method
     * @param string $endpoint
     *
     * @return Response|mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string $endpoint)
    {
        $this->resolvePayload();

        $client = new MockHttpClient($this->buildMockResponse(), $this->getBaseUri());

        return new Response(
            $client->request($method, $endpoint, $this->mergeConfigAndOptions())
        );
    }

    /**
     * @return array<mixed>
     */
    protected function mergeConfigAndOptions(): array
    {
        $headers = array_merge($this->getConfig()['headers'] ?? [], $this->options['headers'] ?? []);
        $options = array_merge($this->getConfig(), $this->options);

        $options['headers'] = $headers;

        return $options;
    }

    /**
     * @return array<mixed>
     */
    public function getRequestOptions(): array
    {
        return $this->mergeConfigAndOptions();
    }

    /**
     * @return string
     */
    protected function getBaseUri()
    {
        $config = $this->config !== null
            ? $this->config->toArray()
            : Config::make()->toArray();

        return isset($config['base_uri']) && is_string($config['base_uri'])
            ? $config['base_uri']
            : 'http://localhost.test';
    }

    /**
     * @return array<mixed>
     */
    protected function getConfig(): array
    {
        $config = $this->config !== null ? $this->config : Config::make();

        return $config->toArray();
    }
}
