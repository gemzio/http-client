<?php

namespace Gemz\HttpClient\Exceptions;

use InvalidArgumentException;

final class InvalidArgument extends InvalidArgumentException
{
    /**
     * @return InvalidArgument
     */
    public static function payloadMustBeArray(): InvalidArgument
    {
        return new static(
            "Payload must be an array when content-type is application/json, " .
            "multipart/form-data or application/x-www-form-urlencoded");
    }

    /**
     * @param string $bodyFormat
     *
     * @return InvalidArgument
     */
    public static function payloadAndBodyFormatNotCompatible(string $bodyFormat): self
    {
        return new static(
            "The body format and the payload are not compatiblePayload must be an array when content-type is application/json, " .
            "multipart/form-data or application/x-www-form-urlencoded");
    }
}
