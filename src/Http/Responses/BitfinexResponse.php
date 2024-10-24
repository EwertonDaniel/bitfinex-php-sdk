<?php

namespace EwertonDaniel\Bitfinex\Http\Responses;

use Closure;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;

abstract class BitfinexResponse
{
    public readonly bool $success;

    public readonly int $statusCode;

    public readonly array $headers;

    public mixed $content;

    public function __construct(Response $response)
    {
        $this->success = $response->getStatusCode() < 300;
        $this->statusCode = $response->getStatusCode();

        if ($this->success) {
            $this->headers = $response->getHeaders();
            $this->content = Utils::jsonDecode($response->getBody()->getContents(), true);
        }
    }

    final public function transformContent(Closure $closure): static
    {
        $this->content = $closure($this->content);

        return $this;
    }
}
