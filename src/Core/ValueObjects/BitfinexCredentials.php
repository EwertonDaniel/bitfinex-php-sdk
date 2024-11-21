<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\ValueObjects;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class BitfinexCredentials
{
    public readonly string $apiKey;

    public readonly string $apiSecret;

    public function __construct(?string $apiKey = null, ?string $apiSecret = null, public ?string $token = null)
    {
        if (is_null($token)) {
            $this->apiKey = GetThis::ifTrueOrFallback($apiKey, $apiKey, fn () => config('bitfinex.api_key'));
            $this->apiSecret = GetThis::ifTrueOrFallback($apiSecret, $apiSecret, fn () => config('bitfinex.api_secret'));
        }
    }

    final public function setToken(string $token): BitfinexCredentials
    {
        $this->token = $token;

        return $this;
    }
}
