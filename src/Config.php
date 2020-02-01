<?php

namespace Gemz\HttpClient;

use Gemz\HttpClient\Contracts\Options;

class Config
{
    use Options;

    /** @var array<mixed> */
    protected $options = [];

    /**
     * @return Config
     */
    public static function build()
    {
        return new self();
    }

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->setDefaults();
    }

    /**
     * @return $this
     */
    protected function setDefaults(): self
    {
        $this->asJson();

        return $this;
    }

    /**
     * Config as array
     *
     * @return array<String|Array>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
