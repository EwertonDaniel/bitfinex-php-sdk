<?php

namespace EwertonDaniel\Bitfinex\Http\Responses;

use Closure;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;
use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;

class BitfinexResponse
{
    private readonly int $statusCode;

    private readonly string $contents;

    private readonly StreamInterface $body;

    private readonly array $array;

    private readonly array $headers;

    public function __construct(private readonly Response $response)
    {
        $this->statusCode = $response->getStatusCode();
        $this->headers = $this->response->getHeaders();
        $this->body = $response->getBody();
        $this->contents = $this->body->getContents();
        $this->array = GetThis::ifTrueOrFallback(
            boolean: $this->statusCode < 300,
            callback: fn () => Utils::jsonDecode($this->contents, true),
            fallback: []
        );
    }

    final public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    final public function getHeaders(): array
    {
        return $this->headers;
    }

    final public function getBody(): StreamInterface
    {
        return $this->body;
    }

    final public function getContents(): string
    {
        return $this->contents;
    }

    final public function result(?Closure $closure = null): array
    {
        return [
            'success' => $this->success(),
            'status' => $this->getStatusCode(),
            'headers' => $this->getHeaders(),
            'body' => GetThis::ifTrueOrFallback(
                boolean: $closure && $this->success(),
                callback: fn () => $closure($this->array),
                fallback: fn () => $this->collect()),
        ];
    }

    final public function success(): bool
    {
        return $this->statusCode < 300;
    }

    final public function collect(): Collection
    {
        return collect($this->array);
    }
}
