<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\ValueObjects;

use function EwertonDaniel\Bitfinex\ValueObjects\config;

class BitfinexCredentials
{
    private readonly string $apiKey;
    private readonly string $apiSecret;

    public function __construct(string|null $apiKey = null, string|null $apiSecret = null)
    {
        $this->apiKey = $apiKey ?? config('bitfinex.api_key');
        $this->apiSecret = $apiSecret ?? config('bitfinex.api_secret');
    }

    final public function getApiKey(): string|null
    {
        return $this->apiKey;
    }

    final public function getApiSecret(): string|null
    {
        return $this->apiSecret;
    }
}
