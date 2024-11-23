<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

class BitfinexSignature
{
    public readonly string $signature;

    public readonly string $nonce;

    public function __construct(string $apiPath, string $body, string $apiSecret)
    {
        /** epoch in ms * 1000 **/
        $this->nonce = (string) (time() * 1000 * 1000);
        $this->signature = hash_hmac('sha384', "/api/{$apiPath}{$this->nonce}{$body}", $apiSecret);
    }
}
