<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

/**
 * Class RequestHeaderBuilder
 *
 * Manages the headers of an HTTP request. Provides methods for adding, resetting, and retrieving
 * headers. Ensures default headers such as `Content-Type` and `Accept` are always set, and allows
 * merging custom headers.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class RequestHeaderBuilder
{
    private array $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    /**
     * Sets or updates a specific header.
     *
     * @param  string  $key  Header name.
     * @param  string  $value  Header value.
     */
    final public function __set(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Merges custom headers with the current headers.
     *
     * @param  array  $headers  Associative array of headers.
     */
    final public function setHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Resets headers to their default values.
     */
    final public function reset(): static
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return $this;
    }

    /**
     * Retrieves all headers.
     *
     * @return array Associative array of headers.
     */
    final public function get(): array
    {
        return $this->headers;
    }
}
