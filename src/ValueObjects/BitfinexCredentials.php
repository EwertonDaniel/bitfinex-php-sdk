<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\BitfinexConfig;

/**
 * Class BitfinexCredentials
 *
 * Represents the API credentials required for authenticating with the Bitfinex API.
 * It handles the API Key, API Secret, and optional token used for authentication.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexCredentials
{
    /** @var string|null API Key for Bitfinex */
    private readonly ?string $apiKey;

    /** @var string|null API Secret for Bitfinex */
    private readonly ?string $apiSecret;

    /**
     * BitfinexCredentials constructor.
     *
     * Initializes the credentials with the provided API Key, API Secret, and optional token.
     * When either is omitted, it is resolved from the configuration or the environment.
     *
     * @param  string|null  $apiKey  API Key for Bitfinex (optional, resolved from config/env if null).
     * @param  string|null  $apiSecret  API Secret for Bitfinex (optional, resolved from config/env if null).
     * @param  string|null  $token  Authentication token (optional, overrides API Key and Secret).
     */
    public function __construct(?string $apiKey = null, ?string $apiSecret = null, private ?string $token = null)
    {
        $this->apiKey = $apiKey ?? BitfinexConfig::string('api_key', 'BITFINEX_API_KEY');
        $this->apiSecret = $apiSecret ?? BitfinexConfig::string('api_secret', 'BITFINEX_API_SECRET');
    }

    /**
     * Sets the authentication token.
     *
     * @param  string  $token  The token to be set for authentication.
     * @return BitfinexCredentials Updated instance with the token set.
     */
    final public function setToken(string $token): BitfinexCredentials
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Retrieves the current authentication token.
     *
     * @return string|null The authentication token, or null if not set.
     */
    final public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Checks if an authentication token is set.
     *
     * @return bool True if a token is set, false otherwise.
     */
    final public function hasToken(): bool
    {
        return ! is_null($this->token);
    }

    /**
     * @throws BitfinexException When no API key was provided or configured.
     */
    final public function getApiKey(): string
    {
        return $this->apiKey ?? throw new BitfinexException(
            'Bitfinex API key is missing. Set it in config/bitfinex.php, in the BITFINEX_API_KEY environment variable, or pass it to BitfinexCredentials.'
        );
    }

    /**
     * @throws BitfinexException When no API secret was provided or configured.
     */
    final public function getApiSecret(): string
    {
        return $this->apiSecret ?? throw new BitfinexException(
            'Bitfinex API secret is missing. Set it in config/bitfinex.php, in the BITFINEX_API_SECRET environment variable, or pass it to BitfinexCredentials.'
        );
    }

    /**
     * Checks whether a key/secret pair is available for signing requests.
     */
    final public function hasApiKeys(): bool
    {
        return ! is_null($this->apiKey) && ! is_null($this->apiSecret);
    }
}
