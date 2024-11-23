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
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
abstract class BitfinexResponse
{
    /** @note Indicates if the request was successful (status code < 300) */
    public readonly bool $success;

    /** @note HTTP status code of the response */
    public readonly int $statusCode;

    /** @note Headers returned in the response */
    public readonly array $headers;

    /** @note Parsed content of the response body */
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

    final public function transformContent(Closure $closure): BitfinexResponse
    {
        $this->content = $closure($this->content);

        return $this;
    }

    protected function wallets(): BitfinexResponse
    {
        return $this;
    }

    protected function generateToken(): BitfinexResponse
    {
        return $this;
    }

    protected function retrieveOrders(): BitfinexResponse
    {
        return $this;
    }

    protected function submitOrder(): BitfinexResponse
    {
        return $this;
    }

    protected function userInfo(): BitfinexResponse
    {
        return $this;
    }

    protected function summary(): BitfinexResponse
    {
        return $this;
    }

    protected function loginHistory(): BitfinexResponse
    {
        return $this;
    }

    protected function keyPermissions(): BitfinexResponse
    {
        return $this;
    }

    protected function changelog(): BitfinexResponse
    {
        return $this;
    }

    protected function movements(): BitfinexResponse
    {
        return $this;
    }

    protected function movementInfo(): BitfinexResponse
    {
        return $this;
    }
}
