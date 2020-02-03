<?php

namespace Gemz\HttpClient;

use Illuminate\Support\Str;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Response
{
    /** @var ResponseInterface */
    protected $response;

    /** @var bool */
    protected $throwErrors;

    /**
     * Response constructor.
     *
     * @param ResponseInterface $response
     * @param bool $throwErrors
     */
    public function __construct(ResponseInterface $response, $throwErrors = false)
    {
        $this->response = $response;
        $this->throwErrors = $throwErrors;
    }

    /**
     * @return int
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isSuccess(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isOk(): bool
    {
        return $this->isSuccess();
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isRedirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isClientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isServerError(): bool
    {
        return $this->status() >= 500;
    }

    /**
     * @return ResponseInterface
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return string
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function body(): string
    {
        return $this->response->getContent($this->throwErrors);
    }

    /**
     * @return string
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function asString(): string
    {
        return $this->body();
    }

    /**
     * @return mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function asObject()
    {
        return json_decode($this->body());
    }

    /**
     * @return \Illuminate\Support\Collection<String|Array>
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function asCollection()
    {
        return collect($this->asArray());
    }

    /**
     * @return mixed
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function asArray()
    {
        return json_decode($this->body(), true);
    }

    /**
     * @return bool
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isJson()
    {
        return $this->contentType() == 'application/json';
    }

    /**
     * @param string $header
     *
     * @return mixed|string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function header(string $header)
    {
        if (array_key_exists(Str::lower($header), $this->headers())) {
            return $this->headers()[$header];
        }

        return '';
    }

    /**
     * @return array<mixed>
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function headers()
    {
        return $this->normalizeHeaders($this->response->getHeaders($this->throwErrors));
    }

    /**
     * @param array<mixed> $headers
     *
     * @return array<String>
     */
    protected function normalizeHeaders(array $headers)
    {
        return collect($headers)->transform(function($item, $key) {
            return $item[0];
        })->all();
    }

    /**
     * @return mixed
     */
    public function requestUrl(): string
    {
        return $this->info()['url'] ?: '';
    }

    /**
     * @return float
     */
    public function executionTime(): float
    {
        return $this->info()['total_time'] ?: 0;
    }

    /**
     * @return array|mixed|null
     */
    public function customData()
    {
        return $this->info()['user_data'] ?? '';
    }

    /**
     * @return mixed|string
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function contentType()
    {
        return $this->header('content-type');
    }

    /**
     * @return array|mixed|null
     */
    public function info()
    {
        return $this->response->getInfo();
    }
}
