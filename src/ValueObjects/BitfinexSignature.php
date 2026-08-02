<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

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
     * Produces a strictly increasing nonce in microseconds since epoch time.
     */
    private static function generateNonce(): int
    {
        $nonce = (int) round(microtime(true) * 1_000_000);

        if ($nonce <= self::$lastNonce) {
            $nonce = self::$lastNonce + 1;
        }

        return self::$lastNonce = $nonce;
    }
}
