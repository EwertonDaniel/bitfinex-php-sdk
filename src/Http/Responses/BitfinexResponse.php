<?php

namespace EwertonDaniel\Bitfinex\Http\Responses;

use Closure;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract Class BitfinexResponse
 *
 * Base class to handle responses from the Bitfinex API. Provides structure for
 * handling status codes, headers, and content transformations for specific API responses.
 *
 * Key Features:
 * - Parses HTTP status codes and headers.
 * - Decodes JSON response bodies for easier data manipulation.
 * - Supports transformation of response content using a custom closure.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
abstract class BitfinexResponse
{
    /** Indicates if the request was successful (status code < 300). */
    public readonly bool $success;

    /** HTTP status code of the response. */
    public readonly int $statusCode;

    /** Headers returned in the response. */
    public readonly array $headers;

    /** Parsed content of the response body. */
    public mixed $content;

    /**
     * Constructor initializes the response properties from the HTTP response.
     *
     * Types the PSR-7 interface rather than Guzzle's concrete class: that is what
     * `ClientInterface::sendRequest()` is declared to return, and it lets a
     * consumer supply a different PSR-7 implementation. Accepting the interface
     * only widens what callers may pass.
     *
     * @param  ResponseInterface  $response  The HTTP response from the Bitfinex API.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->success = $response->getStatusCode() < 300;
        $this->statusCode = $response->getStatusCode();
        $this->headers = $response->getHeaders();

        $body = $response->getBody()->getContents();

        $this->content = GetThis::ifTrueOrFallback(
            boolean: $this->success && $body !== '',
            callback: fn () => json_decode($body, true, 512, JSON_THROW_ON_ERROR),
            fallback: $body
        );
    }

    /**
     * Transforms the content of the response using a custom closure.
     *
     * Returns `static` rather than the base class: every mapper in the subclasses
     * hands this straight back under its own return type, so declaring the base
     * type here was the single cause of all 81 static analysis errors at level 3.
     *
     * @param  Closure  $closure  The closure to transform the content.
     * @return static The current instance with transformed content.
     */
    final protected function transformContent(Closure $closure): static
    {
        $this->content = $closure($this->content);

        return $this;
    }
}
