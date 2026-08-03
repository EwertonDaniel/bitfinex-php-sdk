<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Contracts;

/**
 * Interface NonceProvider
 *
 * Hands out the nonce every authenticated request carries.
 *
 * Bitfinex scopes the nonce to the API key and rejects any request whose nonce
 * is not strictly greater than the last one that key used. The SDK's default
 * counter lives in a single PHP process, so two workers sharing one key can
 * hand out the same microsecond and one of the requests is refused.
 *
 * Implement this to move the counter somewhere both workers can see: Redis
 * `INCR`, a database sequence, or the shipped `LockedFileNonceProvider`.
 *
 * @link https://docs.bitfinex.com/docs/requirements-and-limitations
 */
interface NonceProvider
{
    /**
     * The ceiling the reference documents. A nonce above this is refused.
     *
     * Microseconds since epoch sit around 1.79e15 today, so the sequence has
     * room until roughly the year 2255.
     */
    public const MAX_NONCE = 9007199254740991;

    /**
     * The next nonce, strictly greater than every value handed out before.
     */
    public function next(): int;
}
