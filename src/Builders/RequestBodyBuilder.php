<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use JsonException;
use stdClass;

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
 *
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
     * @param  string  $key  The name of the parameter.
     * @param  mixed  $value  The value of the parameter.
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
     * @param  array  $body  An associative array of parameters to merge.
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
     * An empty body is encoded as `{}` rather than `[]`, since the API expects a JSON object.
     *
     * @return string The JSON representation of the body parameters.
     */
    final public function __toString(): string
    {
        $body = $this->get();

        $payload = GetThis::ifTrueOrFallback(
            boolean: ! empty($body),
            callback: $body,
            fallback: fn () => new stdClass
        );

        try {
            return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // Without this, a failed encode returned false and PHP raised a
            // TypeError on the string return type. It happens twice per request
            // (once to sign, once to send), so the caller saw a TypeError instead
            // of being told which parameter it could not serialize. Invalid UTF-8
            // in `meta` and NAN/INF amounts are the realistic triggers.
            throw new BitfinexException(
                'Could not encode the request body as JSON: '.$e->getMessage().
                '. Check text fields for invalid UTF-8 and numeric fields for NAN or INF.',
                $e->getCode(),
                $e
            );
        }
    }
}
