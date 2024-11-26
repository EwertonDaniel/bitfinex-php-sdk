<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

/**
 * Class RequestBodyBuilder
 *
 * Facilitates the construction and management of HTTP request bodies.
 * Provides methods to dynamically add or update parameters, reset the body,
 * and retrieve the body content in various formats.
 *
 * This class is particularly useful for building structured JSON payloads
 * for API requests, ensuring flexibility and reusability in parameter management.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class RequestBodyBuilder
{
    private array $body = [];

    /**
     * Adds or updates a single body parameter.
     *
     * Dynamically sets a parameter in the request body. If the key already exists,
     * its value will be updated.
     *
     * @param string $key The name of the parameter.
     * @param mixed $value The value of the parameter.
     */
    final public function __set(string $key, mixed $value): void
    {
        $this->body[$key] = $value;
    }

    /**
     * Merges custom parameters into the current body.
     *
     * Combines the provided associative array with the existing body parameters,
     * allowing for batch updates or additions.
     *
     * @param array $body An associative array of parameters to merge.
     * @return static This instance for method chaining.
     */
    final public function setBody(array $body): static
    {
        $this->body = array_merge($this->body, $body);

        return $this;
    }

    /**
     * Resets the body to its initial state.
     *
     * Clears all parameters from the request body, providing a clean slate for building a new payload.
     *
     * @return static This instance for method chaining.
     */
    final public function reset(): static
    {
        $this->body = [];

        return $this;
    }

    /**
     * Retrieves all parameters in the body.
     *
     * Returns the current state of the request body as an associative array.
     *
     * @return array The body parameters.
     */
    final public function get(): array
    {
        return $this->body;
    }

    /**
     * Converts the body parameters to a JSON string.
     *
     * Encodes the current body parameters into a JSON string with unescaped slashes.
     *
     * @return string The JSON representation of the body parameters.
     */
    final public function __toString(): string
    {
        return json_encode($this->get(), JSON_UNESCAPED_SLASHES);
    }
}
