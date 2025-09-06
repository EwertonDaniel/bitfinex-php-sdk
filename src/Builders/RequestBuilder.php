<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexSignature;
use InvalidArgumentException;

/**
 * Class RequestBuilder
 *
 * Provides a robust mechanism for constructing HTTP requests tailored for the Bitfinex API.
 * This class enables flexible configuration of HTTP methods, headers, and request bodies.
 * It ensures compatibility with Bitfinex API requirements, including authentication headers.
 *
 * Key Features:
 * - Dynamic HTTP method validation and setting.
 * - Header and body parameter management with options to add, update, and reset components.
 * - Integration of authentication credentials and signatures for secure API communication.
 * - Retrieval of request components and options as associative arrays for easy use with HTTP clients.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class RequestBuilder
{
    private string $method = "GET";

    public readonly RequestHeaderBuilder $headers;

    public readonly RequestBodyBuilder $body;

    private const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

    /**
     * Constructor initializes headers, query parameters, and body builders.
     */
    public function __construct()
    {
        $this->headers = new RequestHeaderBuilder;
        $this->body = new RequestBodyBuilder;
    }

    /**
     * Sets the HTTP method for the request.
     *
     * @param  string  $method  The HTTP method (e.g., GET, POST).
     * @return static This instance for method chaining.
     *
     * @throws InvalidArgumentException If the provided method is not valid.
     */
    final public function setMethod(string $method): static
    {
        $method = strtoupper($method);

        if (! in_array($method, self::METHODS, true)) {
            throw new InvalidArgumentException("Invalid HTTP method: $method");
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Sets multiple headers for the request.
     *
     * @param  array  $headers  Associative array of headers.
     * @return static This instance for method chaining.
     */
    final public function setHeaders(array $headers): static
    {
        $this->headers->setHeaders($headers);

        return $this;
    }

    /**
     * Configures authentication credentials for the request.
     *
     * Adds the required Bitfinex-specific headers such as nonce, API key, and signature.
     *
     * @param  BitfinexCredentials  $credentials  The API credentials.
     * @param  BitfinexSignature  $signature  The API signature.
     */
    final public function setCredentials(BitfinexCredentials $credentials, BitfinexSignature $signature): void
    {
        $this->headers->__set('bfx-nonce', $signature->nonce);
        $this->headers->__set('bfx-apikey', $credentials->getApiKey());
        $this->headers->__set('bfx-signature', $signature->signature);
    }

    /**
     * Sets the request body with multiple parameters.
     *
     * @param  array  $body  Associative array of body parameters.
     * @return static This instance for method chaining.
     */
    final public function setBody(array $body): static
    {
        $this->body->setBody($body);

        return $this;
    }

    /**
     * Adds a single header to the request.
     *
     * @param  string  $name  The header name.
     * @param  mixed  $value  The header value.
     * @return static This instance for method chaining.
     */
    final public function addHeader(string $name, mixed $value): static
    {
        $this->headers->__set($name, $value);

        return $this;
    }

    /**
     * Adds a single parameter to the request body.
     *
     * @param  string  $name  The parameter name.
     * @param  mixed  $value  The parameter value.
     * @param  bool  $skipEmpty  Whether to skip adding empty values (default: false).
     * @return static This instance for method chaining.
     */
    final public function addBody(string $name, mixed $value, bool $skipEmpty = false): static
    {
        if (! $skipEmpty || ! empty($value)) {
            $this->body->__set($name, $value);
        }

        return $this;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string The HTTP method.
     */
    final public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Retrieves the headers of the request.
     *
     * @return array Associative array of headers.
     */
    final public function getHeaders(): array
    {
        return $this->headers->get();
    }

    /**
     * Retrieves the body of the request.
     *
     * @return array Associative array of body parameters.
     */
    final public function getBody(): array
    {
        return $this->body->get();
    }

    /**
     * Resets all components (headers, body) to their initial state.
     *
     * @return static This instance for method chaining.
     */
    final public function reset(): static
    {
        $this->headers->reset();
        $this->body->reset();

        return $this;
    }

    /**
     * Builds the request as an associative array for HTTP client usage.
     *
     * @return array Associative array containing headers and body.
     */
    final public function getOptions(): array
    {
        $options = [
            'headers' => $this->headers->get(),
            'body' => $this->body->__toString(),
        ];

        $this->reset();

        return $options;
    }
}
