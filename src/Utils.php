<?php

namespace Gemz\HttpClient;

class Utils
{
    /**
     * @param array<String> $headers
     *
     * @return array<String>
     */
    public static function normalizeHeaders(array $headers): array
    {
        $result = [];

        foreach ($headers as $header) {
            [$name, $value] = explode(':', $header, 2);
            $result[$name] = $value;
        }

        return $result;
    }
}
