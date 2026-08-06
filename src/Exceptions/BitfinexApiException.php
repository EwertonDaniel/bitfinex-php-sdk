<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class BitfinexApiException
 *
 * Raised when the Bitfinex API rejects a request.
 *
 * The API reports failure in the body, not in the HTTP status: a rejected order
 * arrives as HTTP 500 carrying `["error", 10001, "Invalid order: not enough
 * exchange balance for ..."]`. Guzzle turns that into a `ServerException` whose
 * message is the HTTP status, so the reason the exchange gave used to be thrown
 * away and the caller saw either a bare `ServerException` or a `TypeError` from
 * building an entity out of an error payload.
 *
 * `apiCode` is the field to branch on. The accompanying text is explicitly
 * documented as free-form and subject to change, so it is carried for humans
 * and never for control flow.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/docs/abbreviations-glossary
 */
class BitfinexApiException extends BitfinexException
{
    /** Request parameters error. */
    public const ERR_PARAMS = 10020;

    /** Generic error: where order rejections land. */
    public const ERR_GENERIC = 10001;

    /** Failed authentication. */
    public const ERR_AUTH_FAIL = 10100;

    /** Error in authentication request nonce: retryable once the nonce advances. */
    public const ERR_AUTH_NONCE = 10114;

    /** Platform in maintenance: retryable. */
    public const ERR_MAINTENANCE = 20060;

    /** Platform not ready: retryable. */
    public const ERR_READY = 11000;

    /**
     * @param  string  $message  Text the API returned, or a description of the body it sent.
     * @param  int|null  $apiCode  Code from the error envelope; null when the API omitted it.
     * @param  int  $httpStatus  HTTP status that carried the error.
     * @param  mixed  $body  Decoded body, or the raw string when it was not JSON.
     */
    public function __construct(
        string $message,
        public readonly ?int $apiCode = null,
        public readonly int $httpStatus = 0,
        public readonly mixed $body = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $apiCode ?? $httpStatus, $previous);
    }

    /**
     * Builds an exception from a response the API rejected.
     */
    public static function fromResponse(?ResponseInterface $response, ?Throwable $previous = null): self
    {
        if (is_null($response)) {
            return new self(
                'The Bitfinex API could not be reached: '.($previous?->getMessage() ?? 'no response'),
                previous: $previous
            );
        }

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();
        $decoded = json_decode($raw, true);

        return self::fromBody($decoded, $status, $raw, $previous)
            ?? new self(
                "The Bitfinex API returned HTTP $status: ".self::summarize($raw),
                httpStatus: $status,
                body: $decoded ?? $raw,
                previous: $previous
            );
    }

    /**
     * Recognizes the documented error bodies, or returns null when the body is not one.
     *
     * Three shapes occur: the `["error", CODE, "MESSAGE"]` envelope (where CODE
     * may be null), a `{"error": "ERR_RATE_LIMIT"}` object on HTTP 429, and a
     * non-JSON HTML page on 404.
     */
    public static function fromBody(mixed $decoded, int $status, string $raw = '', ?Throwable $previous = null): ?self
    {
        if (is_array($decoded) && ($decoded[0] ?? null) === 'error') {
            $code = $decoded[1] ?? null;

            return new self(
                (string) ($decoded[2] ?? 'The Bitfinex API rejected the request.'),
                apiCode: is_int($code) ? $code : null,
                httpStatus: $status,
                body: $decoded,
                previous: $previous
            );
        }

        if (is_array($decoded) && isset($decoded['error']) && is_string($decoded['error'])) {
            return new self(
                'The Bitfinex API rejected the request: '.$decoded['error'],
                httpStatus: $status,
                body: $decoded,
                previous: $previous
            );
        }

        if ($status >= 400) {
            return new self(
                "The Bitfinex API returned HTTP $status: ".self::summarize($raw),
                httpStatus: $status,
                body: $decoded ?? $raw,
                previous: $previous
            );
        }

        return null;
    }

    /**
     * Whether retrying the same request could succeed.
     */
    final public function isRetryable(): bool
    {
        return in_array($this->apiCode, [self::ERR_AUTH_NONCE, self::ERR_MAINTENANCE, self::ERR_READY], true)
            || $this->httpStatus === 429;
    }

    /**
     * Keeps a non-JSON body (an HTML 404 page, for instance) readable in the message.
     */
    private static function summarize(string $raw): string
    {
        $collapsed = trim(preg_replace('/\s+/', ' ', strip_tags($raw)) ?? '');

        if ($collapsed === '') {
            return '(empty body)';
        }

        return mb_substr($collapsed, 0, 200);
    }
}
