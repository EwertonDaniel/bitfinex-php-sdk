<?php

namespace EwertonDaniel\Bitfinex\Http\Responses;

use Closure;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;

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
     * Constructor initializes the response properties from the Guzzle HTTP response.
     *
     * @param  Response  $response  The HTTP response from the Bitfinex API.
     */
    public function __construct(Response $response)
    {
        $this->success = $response->getStatusCode() < 300;
        $this->statusCode = $response->getStatusCode();

        if ($this->success) {
            $this->headers = $response->getHeaders();
            $this->content = Utils::jsonDecode($response->getBody()->getContents(), true);
        }
    }

    /**
     * Transforms the content of the response using a custom closure.
     *
     * @param  Closure  $closure  The closure to transform the content.
     * @return BitfinexResponse The current instance with transformed content.
     */
    final protected function transformContent(Closure $closure): BitfinexResponse
    {
        $this->content = $closure($this->content);

        return $this;
    }
}
