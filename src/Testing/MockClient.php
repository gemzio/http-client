<?php

namespace Gemz\HttpClient\Testing;

use Gemz\HttpClient\Client;
use Gemz\HttpClient\Config;
use Gemz\HttpClient\Response;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockClient extends Client
{
    /** @var MockResponse */
    protected $mockResponse;

    /** @var ResponseInterface */
    protected $response;

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
            $this->mockInfo
        );
    }

    /**
     * @return ResponseInterface
     */
    public function getMockResponse(): ResponseInterface
    {
        return $this->response;
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

        $this->response = $client->request($method, $endpoint, $this->mergeConfigAndOptions());
        return new Response($this->response);
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

        return $config['base_uri'] ?? 'http://localhost.test';
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
