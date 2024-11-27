<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

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
    /** @var string API Key for Bitfinex */
    private readonly string $apiKey;

    /** @var string API Secret for Bitfinex */
    private readonly string $apiSecret;

    /**
     * BitfinexCredentials constructor.
     *
     * Initializes the credentials with the provided API Key, API Secret, and optional token.
     * If a token is provided, it overrides the need for API Key and API Secret.
     *
     * @param  string|null  $apiKey  API Key for Bitfinex (optional, fallback to config if null).
     * @param  string|null  $apiSecret  API Secret for Bitfinex (optional, fallback to config if null).
     * @param  string|null  $token  Authentication token (optional, overrides API Key and Secret).
     */
    public function __construct(?string $apiKey = null, ?string $apiSecret = null, private ?string $token = null)
    {
        if (is_null($token)) {
            $this->apiKey = GetThis::ifTrueOrFallback($apiKey, $apiKey, fn () => config('bitfinex.api_key'));
            $this->apiSecret = GetThis::ifTrueOrFallback($apiSecret, $apiSecret, fn () => config('bitfinex.api_secret'));
        }
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

    final public function getApiKey(): string
    {
        return $this->apiKey;
    }

    final public function getApiSecret(): string
    {
        return $this->apiSecret;
    }
}
