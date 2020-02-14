<?php

namespace Gemz\HttpClient;

use Gemz\HttpClient\Contracts\Options;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class Client
{
    use Options;

    /** @var HttpClientInterface */
    protected $client;

    /** @var Config */
    protected $config;

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
     * @param Config|null $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: new Config();

        $this->transferBodyFormatFromConfig();
        $this->transferThrowErrorsFromConfig();

        $this->client = $this->buildClient();
    }

    /**
     * @return $this
     */
    protected function transferThrowErrorsFromConfig(): self
    {
        $this->throwErrors = $this->config->shouldThrowErrors();

        return $this;
    }

    /**
     * @return $this
     */
    protected function transferBodyFormatFromConfig(): self
    {
        return $this->bodyFormat(
            $this->config->getBodyFormat()
        );
    }

    /**
     * @param string $endpoint
     *
     * @return Response|mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function get(string $endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response|mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function head(string $endpoint)
    {
        return $this->request('HEAD', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response|mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function post(string $endpoint)
    {
        return $this->request('POST', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response|mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function put(string $endpoint)
    {
        return $this->request('PUT', $endpoint);
    }

    /**
     * @param string $endpoint
     *
     * @return Response|mixed
     *
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
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function delete(string $endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * @param string $method
     * @param string $endpoint
     *
     * @return mixed|Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string $endpoint)
    {
        $this->resolvePayload();

        return Response::createFromResponse(
            $this->client->request($method, $endpoint, $this->options),
            $this->throwErrors
        );
    }

    /**
     * @param string $pathToFile
     *
     * @return DataPart
     */
    public static function fileHandler(string $pathToFile): DataPart
    {
        return DataPart::fromPath($pathToFile);
    }

    /**
     * @param ResponseInterface|ResponseInterface[]|iterable $responses
     *
     * @return ResponseStreamInterface
     */
    public function stream($responses): ResponseStreamInterface
    {
        return $this->client->stream(
            collect($responses)->transform(function ($item) {
                return $item->response();
            })
        );
    }

    /**
     * @return HttpClientInterface
     */
    protected function buildClient(): HttpClientInterface
    {
        return HttpClient::create($this->config->toArray());
    }

    /**
     * @return array<String|Array>
     */
    public function config(): array
    {
        return $this->config->toArray();
    }

    /**
     * @return array<mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return HttpClientInterface
     */
    public function client(): HttpClientInterface
    {
        return $this->client;
    }
}
