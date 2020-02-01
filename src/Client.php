<?php

namespace Gemz\HttpClient;

use Gemz\HttpClient\Contracts\Options;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class Client
{
    use Options;

    /** @var \Symfony\Contracts\HttpClient\HttpClientInterface */
    protected $client;

    /** @var Config|null */
    protected $config;

    /** @var array<mixed> */
    protected $options = [];

    /** @var bool */
    protected $ignoreConfig = false;

    /** @var bool */
    protected $throwErrors = false;

    /**
     * @param Config|null $config
     *
     * @return Client
     */
    public static function create(Config $config = null)
    {
        return new self($config);
    }

    /**
     * Client constructor.
     *
     * @param Config|null $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: new Config();
        $this->transferContentTypeFromConfig();

        $this->client = $this->buildClient();
    }

    /**
     * @return $this
     */
    protected function transferContentTypeFromConfig(): self
    {
        return $this->contentType(
            $this->config->getContentType()
        );
    }

    /**
     * Ignores the config settings
     *
     * @return $this
     */
    public function ignoreConfig(): self
    {
        $this->ignoreConfig = true;

        return $this;
    }

    /**
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function get(string $endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function post(string $endpoint)
    {
        return $this->request('POST', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function put(string $endpoint)
    {
        return $this->request('PUT', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function patch(string $endpoint)
    {
        return $this->request('PATCH', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function delete(string $endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * @return $this
     */
    public function throwErrors(): self
    {
        $this->throwErrors = true;

        return $this;
    }

    /**
     * Handles the request
     *
     * @param string $method
     * @param string $endpoint
     *
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string $endpoint)
    {
        $response = $this->ignoreConfig
            ? self::create()->request($method, $endpoint, $this->options)
            : $this->client->request($method, $endpoint, $this->options);

        return new Response($response, $this->throwErrors);
    }

    /**
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    protected function buildClient()
    {
        return HttpClient::create($this->config->toArray());
    }
 }
