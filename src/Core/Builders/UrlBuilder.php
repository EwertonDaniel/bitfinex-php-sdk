<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Builders;

use EwertonDaniel\Bitfinex\Adapters\PathAdapter;
use EwertonDaniel\Bitfinex\Adapters\UrlAdapter;
use Exception;
use Illuminate\Support\Arr;

class UrlBuilder
{
    private readonly array $urls;

    private readonly array $paths;

    private string $baseUrl;

    private string $path;

    /**  @throws Exception */
    public function __construct()
    {
        $this->urls = (new UrlAdapter)->transform();
        $this->paths = (new PathAdapter)->transform();
    }

    /**
     * @throws Exception
     */
    final public function setBaseUrl(string $type): static
    {
        $url = Arr::get($this->urls, $type);

        if (is_null($url)) {
            throw new Exception("Url not found: {$type}");
        }

        $this->baseUrl = $url;

        return $this;
    }

    final public function get(): string
    {
        $url = "{$this->baseUrl}/$this->path";
        $this->resetPath();

        return $url;
    }

    public function resetPath(): static
    {
        $this->path = '';

        return $this;
    }

    public function resetUrl(): static
    {
        $this->baseUrl = '';

        return $this;
    }

    /** @throws Exception */
    final public function setPath(string $path, array $params = []): static
    {
        $path = Arr::get($this->paths, $path);

        if (is_null($path)) {
            throw new Exception("Path not found: {$path}");
        }

        if (! empty($params)) {
            $path = str_replace(array_keys($params), array_values($params), $path);
        }

        $this->path = $path;

        return $this;
    }
}
