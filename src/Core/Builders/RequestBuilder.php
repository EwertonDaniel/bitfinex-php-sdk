<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Builders;

use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexSignature;
use InvalidArgumentException;

/**
 * Class RequestBuilder
 *
 * Builds a HTTP request for the Bitfinex API, including headers, query parameters, and body.
 * Provides methods to set HTTP method, headers, query parameters, and body with flexible options
 * for resetting and retrieving the built components. Ensures that HTTP methods and required
 * headers adhere to API specifications.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class RequestBuilder
{
    private readonly string $method;

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
     * @param  string  $method  HTTP method (GET, POST, etc.)
     *
     * @throws InvalidArgumentException If the method is not valid.
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
     */
    final public function setHeaders(array $headers): static
    {
        $this->headers->setHeaders($headers);

        return $this;
    }

    /**
     * Configures authentication credentials for the request.
     * Adds necessary Bitfinex-specific headers such as nonce, API key, and signature.
     *
     * @param  BitfinexCredentials  $credentials  API credentials.
     * @param  BitfinexSignature  $signature  API signature.
     */
    final public function setCredentials(BitfinexCredentials $credentials, BitfinexSignature $signature): void
    {
        $this->headers->__set('bfx-nonce', $signature->nonce);
        $this->headers->__set('bfx-apikey', $credentials->apiKey);
        $this->headers->__set('bfx-signature', $signature->signature);
    }

    /**
     * Sets the request body.
     *
     * @param  array  $body  Associative array of body parameters.
     */
    final public function setBody(array $body): static
    {
        $this->body->setBody($body);

        return $this;
    }

    /**
     * Adds a single header to the request.
     *
     * @param  string  $name  Header name.
     * @param  mixed  $value  Header value.
     */
    final public function addHeader(string $name, mixed $value): static
    {
        $this->headers->__set($name, $value);

        return $this;
    }

    /**
     * Adds a single body parameter to the request.
     *
     * @param  string  $name  Body parameter name.
     * @param  mixed  $value  Body parameter value.
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
     * @return string HTTP method.
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
     * Resets all components (headers, query parameters, body) to their initial state.
     */
    final public function reset(): static
    {
        $this->headers->reset();
        $this->body->reset();

        return $this;
    }

    /**
     * Builds the request as an associative array.
     *
     * @return array Associative array with method, headers, query parameters, and body.
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
