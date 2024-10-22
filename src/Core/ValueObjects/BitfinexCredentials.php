<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\ValueObjects;

class BitfinexCredentials
{
    private readonly string $apiKey;

    private readonly string $apiSecret;

    public function __construct(?string $apiKey = null, ?string $apiSecret = null)
    {
        $this->apiKey = $apiKey ?? config('bitfinex.api_key');
        $this->apiSecret = $apiSecret ?? config('bitfinex.api_secret');
    }

    final public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    final public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }
}
