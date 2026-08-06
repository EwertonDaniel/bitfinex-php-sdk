<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

use EwertonDaniel\Bitfinex\Adapters\PathAdapter;
use EwertonDaniel\Bitfinex\Adapters\UrlAdapter;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexFileNotFoundException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexUrlNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\BitfinexConfig;
use Illuminate\Support\Arr;

/**
 * Class UrlBuilder
 *
 * This class is responsible for constructing URLs for the Bitfinex API, combining base URLs
 * and paths defined by adapters. It supports dynamic URL and path management with parameter
 * replacement and error handling using custom exceptions.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class UrlBuilder
{
    /**
     * @var array Stores the base URLs loaded from the UrlAdapter.
     */
    private readonly array $urls;

    /**
     * @var array Stores the API paths loaded from the PathAdapter.
     */
    private readonly array $paths;

    /**
     * @var string The base URL currently in use.
     */
    private string $baseUrl;

    /**
     * @var string The API path currently in use.
     */
    private string $path;

    /**
     * Constructor initializes URLs and paths by transforming data from adapters.
     *
     * Base URLs configured through `config/bitfinex.php` or the environment take
     * precedence over the ones shipped in `resources/urls.json`.
     *
     * @throws BitfinexFileNotFoundException
     */
    public function __construct()
    {
        $configuredUrls = array_filter([
            'public' => BitfinexConfig::string('urls.public', 'BITFINEX_PUBLIC_URL'),
            'private' => BitfinexConfig::string('urls.private', 'BITFINEX_PRIVATE_URL'),
        ]);

        $this->urls = array_merge(
            (new UrlAdapter)->transform(),
            array_map(fn (string $url) => rtrim($url, '/'), $configuredUrls)
        );

        $this->paths = (new PathAdapter)->transform();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets the base URL based on the provided type.
     *
     * @param  string  $type  The type of base URL (e.g., 'public', 'private').
     * @return static Returns the instance for method chaining.
     *
     * @throws BitfinexUrlNotFoundException If the specified URL type is not found.
     */
    final public function setBaseUrl(string $type): static
    {
        $url = Arr::get($this->urls, $type);

        if (is_null($url)) {
            throw new BitfinexUrlNotFoundException($type);
        }

        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Builds and retrieves the full URL by combining the base URL and the path.
     * Resets the path after constructing the URL.
     *
     * @return string The constructed full URL.
     */
    final public function get(): string
    {
        $url = "{$this->baseUrl}/$this->path";
        $this->resetPath();

        return $url;
    }

    /**
     * Resets the current path to an empty string.
     *
     * @return static Returns the instance for method chaining.
     */
    public function resetPath(): static
    {
        $this->path = '';

        return $this;
    }

    /**
     * Resets the current base URL to an empty string.
     *
     * @return static Returns the instance for method chaining.
     */
    public function resetUrl(): static
    {
        $this->baseUrl = '';

        return $this;
    }

    /**
     * Sets the API path based on the provided path identifier.
     * Replaces placeholders in the path with corresponding parameters.
     *
     * @param  string  $path  The identifier for the path (e.g., 'ticker', 'order').
     * @param  array  $params  An associative array of parameters to replace in the path.
     * @return static Returns the instance for method chaining.
     *
     * @throws BitfinexPathNotFoundException If the specified path identifier is not found.
     */
    final public function setPath(string $path, array $params = []): static
    {
        $foundPath = Arr::get($this->paths, $path);

        if (is_null($foundPath)) {
            throw new BitfinexPathNotFoundException($path);
        }

        if (! empty($params)) {
            $keys = array_map(fn ($key) => "{{$key}}", array_keys($params));
            $values = array_map(fn ($value) => self::encodeSegment((string) $value), array_values($params));
            $foundPath = str_replace($keys, $values, $foundPath);
        }

        $this->path = $foundPath;

        return $this;
    }

    /**
     * Percent-encodes a value so it cannot escape the path segment it belongs to.
     *
     * Interpolating a raw value let a caller-supplied `?` or `#` truncate the
     * path, so the URI that went on the wire stopped matching the one that was
     * signed and the request landed on a different endpoint. A literal space had
     * the same effect the other way around: Guzzle encoded it on send while the
     * signature was computed over the raw character.
     *
     * Only characters a path segment does not allow are encoded. `:` is legal
     * there per RFC 3986 and is load-bearing for symbols such as
     * `tBTCF0:USTF0` and configuration keys such as `pub:list:pair:exchange`,
     * so it is deliberately preserved.
     */
    private static function encodeSegment(string $value): string
    {
        return preg_replace_callback(
            '/[^A-Za-z0-9\-._~:@!$&\'()*+,;=]/',
            fn (array $matches) => rawurlencode($matches[0]),
            $value
        );
    }

    final public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
