<?php

namespace Gemz\HttpClient\Contracts;

use Illuminate\Support\Str;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

trait Options
{
    /**
     * Set authentication auth bearer
     *
     * @param string $token
     *
     * @return $this
     */
    public function authBearer(string $token): self
    {
        return $this->option('auth_bearer', $token);
    }

    /**
     * Set authentication auth basic
     *
     * @param string $username
     * @param string|null $password
     *
     * @return $this
     */
    public function authBasic(string $username, $password = null): self
    {
        $authValues = $password == null
            ? [$username]
            : [$username, $password];

        return $this->option('auth_basic', $authValues);
    }

    /**
     * Values for existing keys will be replaced
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function header(string $key, string $value): self
    {
        $this->options['headers'][Str::lower($key)] = $value;

        return $this;
    }

    /**
     * Values for existing keys will be replaced
     *
     * @param array<String> $headers
     *
     * @return $this
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }

        return $this;
    }

    /**
     * Set the base uri
     *
     * @param string $uri
     *
     * @return $this
     */
    public function baseUri(string $uri): self
    {
        return $this->option('base_uri', $uri);
    }

    /**
     * Max duration the whole request response time could take
     * 0 indicates infinite duration
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function maxDuration(int $seconds): self
    {
        return $this->option('max_duration', $seconds);
    }

    /**
     * Default value is 20. 0 indicates unlimited redirects
     *
     * @param int $value
     *
     * @return $this
     */
    public function maxRedirects(int $value): self
    {
        return $this->option('max_redirects', $value);
    }

    /**
     * float - the idle timeout - defaults to ini_get('default_socket_timeout')
     *
     * @param float $value
     *
     * @return $this
     */
    public function timeout(float $value): self
    {
        return $this->option('timeout', $value);
    }

    /**
     * Does not verify SSL connections
     *
     * @return $this
     */
    public function withoutVerifying(): self
    {
        return $this->option('verify_peer', false);
    }

    /**
     * Set the content type
     *
     * @param string $type
     *
     * @return $this
     */
    public function contentType(string $type): self
    {
        return $this->header('content-type', $type);
    }

    /**
     * Set the clients user agent
     *
     * @param string $agent
     *
     * @return $this
     */
    public function userAgent(string $agent): self
    {
        return $this->header('User-Agent', $agent);
    }

    /**
     * Set accept header
     *
     * @param string $value
     *
     * @return $this
     */
    public function accept(string $value): self
    {
        return $this->header('Accept', $value);
    }

    /**
     * Set option according to symfony HttpClientInterface
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function option(string $key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }


    /**
     * Set param for the query url
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function queryParam(string $key, string $value): self
    {
        $this->options['query'][$key] = $value;

        return $this;
    }

    /**
     * Set multiple params for query url
     * form [<key> => <value>]
     *
     * @param array<String> $params
     *
     * @return $this
     */
    public function queryParams(array $params): self
    {
        foreach ($params as $key => $value) {
            $this->queryParam($key, $value);
        }

        return $this;
    }

    /**
     * Any extra data to attach to the request (scalar, callable, object...)
     * Available in response->getCustomData(). Useful when using asynchronous requests
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function customData($data): self
    {
        $this->option('user_data', $data);

        return $this;
    }

    /**
     * @return $this
     */
    public function asPlainText()
    {
        return $this->contentType('text/plain');
    }

    /**
     * @return $this
     */
    public function asJson(): self
    {
        return $this->contentType('application/json');
    }

    /**
     * @return $this
     */
    public function asFormParams(): self
    {
        return $this->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Set the content type multipart form-data
     *
     * @return $this
     */
    public function asMultipart(): self
    {
        return $this->contentType('multipart/form-data');
    }

    /**
     * @return array<mixed>
     */
    public function getHeaders(): array
    {
        return $this->options['headers'] ?: [];
    }

    /**
     * @return null|string
     */
    public function getContentType()
    {
        return array_key_exists('content-type', $this->getHeaders())
            ? $this->getHeaders()['content-type']
            : null;
    }

    /**
     * Set any payload text, array
     *
     * @param mixed $payload
     *
     * @return $this
     */
    public function payload($payload): self
    {
        $types = [
            'application/json',
            'multipart/form-data',
            'application/x-www-form-urlencoded'
        ];

        $contentType = $this->getContentType();

        if (! is_array($payload) && in_array($contentType, $types)) {
            throw new \InvalidArgumentException(
                'payload must be an array when content-type is application/json,' .
                         ' multipart/form-data or application/x-www-form-urlencoded'
            );
        }

        if ($contentType == 'application/json') {
            return $this->option('json', $payload);
        }

        if ($contentType == 'multipart/form-data') {
            $formData = new FormDataPart($payload);
            $payload = $formData->bodyToIterable();

            $this->headers($formData->getPreparedHeaders()->toArray());
        }

        return $this->option('body', $payload);
    }

    /**
     * @param mixed $body
     *
     * @return $this
     */
    protected function body($body): self
    {
        return $this->option('body', $body);
    }

}
