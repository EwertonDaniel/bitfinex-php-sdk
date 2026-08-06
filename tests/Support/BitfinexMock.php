<?php

declare(strict_types=1);

namespace Tests\Support;

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Drives the authenticated services against a queued set of responses instead of
 * the exchange.
 *
 * The Feature suite used to run against the real API with placeholder credentials,
 * so every authenticated test failed on `apikey: digest invalid` and asserted
 * nothing about the code under test. Writing to the real API is not an option
 * either: `.claude/IMMUTABLE_RULES.md` Regra #2 forbids it, and it moves real
 * money. A queued Guzzle handler gives the tests something to actually assert.
 *
 * The recorded requests are kept, so a test can check what went on the wire —
 * path, headers, body — which is the half of the contract a response fixture
 * cannot cover.
 */
final class BitfinexMock
{
    /** @var list<array{request: RequestInterface, response: ResponseInterface}> */
    private array $history = [];

    private readonly Client $client;

    /** @param list<array|ResponseInterface|Throwable> $responses Queued in call order. */
    private function __construct(array $responses)
    {
        $handler = new MockHandler(array_map(self::toPsrResponse(...), $responses));

        $stack = HandlerStack::create($handler);
        $stack->push(Middleware::history($this->history));

        $this->client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://api.bitfinex.com',
        ]);
    }

    /**
     * Queues the responses the exchange is meant to give, in call order.
     *
     * A plain array becomes a 200 carrying it as JSON. A `Response` or a
     * `Throwable` is queued as is, for the failure paths.
     *
     * @param  list<array|ResponseInterface|Throwable>  $responses
     */
    public static function queue(array $responses): self
    {
        return new self($responses);
    }

    /**
     * An authenticated service wired to the queue.
     *
     * The credentials are never verified by the queue, so any value does; they
     * exist only because the signing path runs for real on the way out.
     */
    public function authenticated(?BitfinexCredentials $credentials = null): BitfinexAuthenticated
    {
        return (new Bitfinex)->authenticated(
            $credentials ?? new BitfinexCredentials(apiKey: 'test-key', apiSecret: 'test-secret'),
            $this->client
        );
    }

    /** The client itself, for the few tests that build their own service. */
    public function client(): Client
    {
        return $this->client;
    }

    /** @return list<RequestInterface> Every request sent, in order. */
    public function requests(): array
    {
        return array_map(fn (array $entry) => $entry['request'], $this->history);
    }

    /** @return list<string> The paths requested, in order. */
    public function paths(): array
    {
        return array_map(fn (RequestInterface $request) => $request->getUri()->getPath(), $this->requests());
    }

    /** The decoded JSON body of the nth request, counting from zero. */
    public function bodyOf(int $index): array
    {
        $request = $this->requests()[$index] ?? null;

        if (is_null($request)) {
            return [];
        }

        return json_decode((string) $request->getBody(), true) ?? [];
    }

    /**
     * The headers of the nth request, counting from zero, lower-cased and
     * flattened to one value each. HTTP header names are case insensitive, so a
     * test asserting on them should not depend on how they were spelled.
     */
    public function headersOf(int $index): array
    {
        $request = $this->requests()[$index] ?? null;

        if (is_null($request)) {
            return [];
        }

        $headers = [];

        foreach ($request->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = $values[0];
        }

        return $headers;
    }

    private static function toPsrResponse(mixed $response): ResponseInterface|Throwable
    {
        if ($response instanceof ResponseInterface || $response instanceof Throwable) {
            return $response;
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }
}
