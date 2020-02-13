<?php

namespace Gemz\HttpClient;

use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Stream
{
    /** @var string */
    const STATUS_FULFILLED = 'FULFILLED';

    /** @var string */
    const STATUS_REJECTED = 'REJECTED';

    /** @var string */
    const STATUS_TIMEOUT = 'TIMEOUT';

    /** @var string */
    const STATUS_PENDING = 'PENDING';

    /** @var ResponseInterface */
    protected $response;

    /** @var ChunkInterface */
    protected $chunk;

    /** @var string */
    protected $status = self::STATUS_PENDING;

    /** @var TransportExceptionInterface */
    protected $exception;

    /**
     * @param ResponseInterface $response
     * @param ChunkInterface $chunk
     *
     * @return Stream
     */
    public static function from(ResponseInterface $response, ChunkInterface $chunk)
    {
        return new self($response, $chunk);
    }

    /**
     * @param ResponseInterface $response
     * @param ChunkInterface $chunk
     */
    public function __construct(ResponseInterface $response, ChunkInterface $chunk)
    {
        $this->response = $response;
        $this->chunk = $chunk;

        $this->detectStatus();
    }

    protected function detectStatus(): void
    {
        try {
            if ($this->chunk->isLast()) {
                $this->setStatus(self::STATUS_FULFILLED);
            }

            if ($this->chunk->isTimeout()) {
                $this->setStatus(self::STATUS_TIMEOUT);
            }
        } catch (TransportExceptionInterface $e) {
            $this->setStatus(self::STATUS_REJECTED);

            $this->exception = $e;
        }
    }

    /**
     * @return string
     */
    protected function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    protected function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    protected function isPending(): bool
    {
        return $this->getStatus() == 'PENDING';
    }

    /**
     * @return bool
     */
    protected function isFulfilled(): bool
    {
        return $this->getStatus() == 'FULFILLED';
    }

    /**
     * @return bool
     */
    protected function isTimeout(): bool
    {
        return $this->getStatus() == 'TIMEOUT';
    }

    /**
     * @return bool
     */
    protected function isRejected(): bool
    {
        return $this->getStatus() == 'REJECTED';
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function then(callable $callback): self
    {
        if ($this->isFulfilled()) {
            $callback($this->response());
        }

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function timeout(callable $callback): self
    {
        if ($this->isTimeout()) {
            $callback($this->response());
        }

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function catch(callable $callback): self
    {
        if ($this->isRejected()) {
            $callback($this->exception, $this->response());
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function response(): Response
    {
        return Response::createFromResponse($this->response);
    }
}
