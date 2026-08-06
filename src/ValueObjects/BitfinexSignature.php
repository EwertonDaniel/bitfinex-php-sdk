<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

use EwertonDaniel\Bitfinex\Contracts\NonceProvider;

/**
 * Class BitfinexSignature
 *
 * Represents the cryptographic signature and nonce required for authenticating requests
 * to the Bitfinex API. The signature is generated using HMAC-SHA384 and combines the API
 * path, nonce, and request body.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexSignature
{
    /**
     * The cryptographic signature used for authenticating the API request.
     */
    public readonly string $signature;

    /**
     * A unique value (nonce) generated in microseconds since epoch time.
     * This ensures that each request has a unique signature.
     */
    public readonly string $nonce;

    /**
     * Last nonce handed out by this process.
     *
     * @note microtime() resolution is not fine enough to guarantee distinct values
     *       between consecutive calls, and Bitfinex rejects any nonce that is not
     *       greater than the previous one used by the same API key.
     */
    private static int $lastNonce = 0;

    /**
     * Counter shared beyond this process, when one was installed.
     *
     * Null means the in-process counter below, which is correct for a single
     * worker and collides for several sharing one API key.
     */
    private static ?NonceProvider $provider = null;

    /**
     * Constructs a new `BitfinexSignature` instance.
     *
     * @param  string  $apiPath  The API endpoint path (e.g., 'private/account_actions.generate_token').
     * @param  string  $body  The request body as a string.
     * @param  string  $apiSecret  The API secret key used to generate the HMAC signature.
     */
    public function __construct(string $apiPath, string $body, string $apiSecret)
    {
        $this->nonce = (string) self::generateNonce();

        $this->signature = hash_hmac('sha384', "/api/{$apiPath}{$this->nonce}{$body}", $apiSecret);
    }

    /**
     * Hands out a nonce for a request that is authenticated by token.
     *
     * Token authentication replaces the key and the signature, not the nonce, so
     * token-mode requests draw from the same strictly increasing sequence as
     * signed ones.
     *
     * Bitfinex scopes the nonce to the API key and requires it to be strictly
     * increasing. The default counter only spans one process, so several workers
     * sharing a key still collide: either run one API key per client, as the
     * official guidance says, or install a shared counter with
     * `useNonceProvider()`.
     *
     * @link https://docs.bitfinex.com/docs/requirements-and-limitations
     */
    public static function nonce(): string
    {
        return (string) self::generateNonce();
    }

    /**
     * Installs a counter shared beyond this process.
     *
     * Needed whenever more than one process signs with the same API key: PHP-FPM
     * workers, queue consumers, a scheduler running alongside a web request. Pass
     * null to go back to the in-process counter.
     *
     * ```php
     * BitfinexSignature::useNonceProvider(
     *     new LockedFileNonceProvider('/var/run/bitfinex/nonce-'.$keyId)
     * );
     * ```
     */
    public static function useNonceProvider(?NonceProvider $provider): void
    {
        self::$provider = $provider;
    }

    /**
     * Produces a strictly increasing nonce in microseconds since epoch time.
     *
     * A provider's value is trusted as-is: it is the shared sequence, and forcing
     * it above this process's own last value would defeat the point of sharing.
     */
    private static function generateNonce(): int
    {
        if (! is_null(self::$provider)) {
            return self::$provider->next();
        }

        $nonce = (int) round(microtime(true) * 1_000_000);

        if ($nonce <= self::$lastNonce) {
            $nonce = self::$lastNonce + 1;
        }

        return self::$lastNonce = $nonce;
    }
}
