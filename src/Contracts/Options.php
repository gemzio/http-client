<?php

namespace Gemz\HttpClient\Contracts;

use Gemz\HttpClient\Exceptions\InvalidArgument;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

trait Options
{
    /** @var string */
    private static $CONTENT_TYPE_JSON = 'application/json';

    /** @var string */
    private static $CONTENT_TYPE_PLAIN = 'text/plain';

    /** @var string */
    private static $CONTENT_TYPE_MULTIPART = 'multipart/form-data';

    /** @var string */
    private static $CONTENT_TYPE_FORM_PARAMS = 'application/x-www-form-urlencoded';

    /** @var string */
    protected static $CUSTOM_DATA_HEADER = 'X-Custom-Data';

    /** @var array<mixed> */
    protected $options = [];

    /** @var bool */
    protected $throwErrors = false;

    /** @var array<String> */
    protected $bodyFormats = ['json', 'multipart', 'form_params'];

    /** @var string */
    protected $bodyFormat = 'json';

    /**
     * Set authentication auth bearer token
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
     * Set authentication auth basic. If password is null
     * only username will be used
     *
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function authBasic(string $username, string $password = ''): self
    {
        $this->options['auth_basic'] = $username;

        if ('' !== $password) {
            $this->options['auth_basic'] .= ':' . $password;
        }

        return $this;
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
     * @return bool
     */
    public function shouldThrowErrors(): bool
    {
        return $this->throwErrors;
    }

    /**
     * Values for existing header keys will be replaced
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function header(string $key, string $value): self
    {
        $this->options['headers'][$key] = $value;

        return $this;
    }

    /**
     * Values for existing header keys will be replaced
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
     * Set the base uri.
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
     * Disallows redirects.
     *
     * @return $this
     */
    public function disallowRedirects(): self
    {
        return $this->option('max_redirects', -1);
    }

    /**
     * @param int $max
     *
     * @return $this
     */
    public function allowRedirects(int $max = 0): self
    {
        return $this->option('max_redirects', $max);
    }

    /**
     * Float describing the timeout of the request in seconds.
     * use 0 to wait indefinitely
     *
     * @param float $seconds
     *
     * @return $this
     */
    public function timeout(float $seconds): self
    {
        return $this->option('timeout', $seconds);
    }

    /**
     * Pass a string to specify an HTTP proxy, or an array to specify different proxies for different protocols.
     *
     * @param string|array<String> $proxy
     *
     * @return $this
     */
    public function useProxy($proxy): self
    {
        return $this->option('proxy', $proxy);
    }

    /**
     * @param float $seconds
     *
     * @return $this
     */
    public function maxDuration(float $seconds): self
    {
        return $this->option('max_duration', $seconds);
    }

    /**
     * Does not verify SSL certificates
     *
     * @return $this
     */
    public function doNotVerifySsl(): self
    {
        $this->option('verify_peer', false);
        $this->option('verify_host', false);

        return $this;
    }

    /**
     * Does verify SSL certificates
     *
     * @return $this
     */
    public function verifySsl(): self
    {
        $this->option('verify_peer', true);
        $this->option('verify_host', true);

        return $this;
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
        return $this->header('Content-Type', $type);
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
     * Set option according guzzle request options
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
     * in form of [<key> => <value>, <key2> => <value2>]
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
     * Any extra data to attach to the response header
     * Available in response->customData(). Useful when using asynchronous requests
     * to identify the request
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function customData($data): self
    {
        return $this->option('user_data', $data);
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    protected function bodyFormat(string $format): self
    {
        $this->bodyFormat = $format;

        return $this;
    }

    /**
     * @return $this
     */
    public function asPlainText()
    {
        $this->bodyFormat('body');

        return $this->contentType(self::$CONTENT_TYPE_PLAIN);
    }

    /**
     * @return $this
     */
    public function asJson(): self
    {
        $this->bodyFormat('json');

        return $this->contentType(self::$CONTENT_TYPE_JSON);
    }

    /**
     * @return $this
     */
    public function asFormParams(): self
    {
        $this->bodyFormat('form_params');

        return $this->contentType(self::$CONTENT_TYPE_FORM_PARAMS);
    }

    /**
     * Set the content type multipart form-data
     *
     * @return $this
     */
    public function asMultipart(): self
    {
        $this->bodyFormat('multipart');

        return $this->contentType(self::$CONTENT_TYPE_MULTIPART);
    }

    /**
     * @return array<mixed>
     */
    public function getHeaders(): array
    {
        return $this->options['headers'] ?? [];
    }

    /**
     * @return null|string
     */
    public function getContentType()
    {
        return array_key_exists('Content-Type', $this->getHeaders())
            ? $this->getHeaders()['Content-Type']
            : null;
    }

    /**
     * @return string
     */
    public function getBodyFormat(): string
    {
        return $this->bodyFormat;
    }

    /**
     * Indicates if the payload must be an array
     * depending on body format
     *
     * @return bool
     */
    protected function payloadMustBeArray(): bool
    {
        return in_array(
            $this->bodyFormat,
            $this->bodyFormats
        );
    }

    /**
     * @param mixed $payload
     *
     * @return $this
     */
    protected function throwExceptionWhenPayloadIsNotArray($payload): self
    {
        if (! is_array($payload)) {
            throw InvalidArgument::payloadMustBeArray();
        }

        return $this;
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
        return $this->option('payload', $payload);
    }

    /**
     * @param string $option
     *
     * @return mixed
     */
    protected function getOption(string $option)
    {
        return $this->options[$option] ?? '';
    }

    /**
     * @return $this
     */
    protected function resolvePayload(): self
    {
        $payload = $this->getOption('payload');

        if (! is_array($payload) && $this->payloadMustBeArray()) {
            throw InvalidArgument::payloadMustBeArray();
        }

        if (empty($payload) || $payload == null) {
            $this->removeOptions(['body', 'payload']);

            return $this;
        }

        if ($this->bodyFormat == 'json') {
            $this->option('json', $payload);
            $this->removeOptions(['body', 'payload']);

            return $this;
        }

        if ($this->bodyFormat == 'multipart') {
            $formData = new FormDataPart($payload);

            $this->headers($formData->getPreparedHeaders()->toArray());
            $this->option('body', $formData->bodyToIterable());
            $this->removeOptions(['json', 'payload']);

            return $this;
        }

        if ($this->bodyFormat == 'form_params') {
            $this->option('body', $payload);
            $this->removeOptions(['json', 'payload']);

            return $this;
        }

        if (is_string($payload)
            || is_resource($payload)
            || is_callable($payload)) {

            $this->option('body', $payload);
            $this->removeOptions(['json', 'payload']);

            return $this;
        } else {
            throw InvalidArgument::payloadAndBodyFormatNotCompatible($this->bodyFormat);
        }
    }

    /**
     * remove an option
     *
     * @param string $option
     *
     * @return $this
     */
    protected function removeOption(string $option): self
    {
        unset($this->options[$option]);

        return $this;
    }

    /**
     * @param array<String> $options
     *
     * @return $this
     */
    protected function removeOptions(array $options): self
    {
        foreach ($options as $option) {
            if (! is_string($option)) {
                continue;
            }

            $this->removeOption($option);
        }

        return $this;
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
