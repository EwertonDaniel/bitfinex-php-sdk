<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Builders;

/**
 * Class RequestBodyBuilder
 *
 * Manages the body of an HTTP request. Allows adding or updating body parameters,
 * resetting the body, and retrieving all parameters. Useful for building structured
 * JSON payloads for API requests.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class RequestBodyBuilder
{
    private array $body = [];

    /**
     * Adds or updates a single body parameter.
     *
     * @param  string  $key  Parameter name.
     * @param  mixed  $value  Parameter value.
     */
    final public function __set(string $key, mixed $value): void
    {
        $this->body[$key] = $value;
    }

    /**
     * Merges custom body parameters with the current body.
     *
     * @param  array  $body  Associative array of body parameters.
     */
    final public function setBody(array $body): static
    {
        $this->body = array_merge($this->body, $body);

        return $this;
    }

    /**
     * Resets the body to an empty state.
     */
    final public function reset(): static
    {
        $this->body = [];

        return $this;
    }

    /**
     * Retrieves all body parameters.
     *
     * @return array Associative array of body parameters.
     */
    final public function get(): array
    {
        return $this->body;
    }

    final public function __toString(): string
    {
        return json_encode($this->get(), JSON_UNESCAPED_SLASHES);
    }
}
