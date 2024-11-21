<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexFileNotFoundException;
use GuzzleHttp\Utils;

abstract class JsonAdapter
{
    protected string $file;

    public function __construct()
    {
        $this->file = $this->getFilePath();
    }

    abstract protected function getFilePath(): string;

    /** @throws BitfinexFileNotFoundException */
    public function transform(): array
    {
        if (! file_exists($this->file)) {
            throw new BitfinexFileNotFoundException($this->file);
        }

        $contents = file_get_contents($this->file);

        return Utils::jsonDecode($contents, true);
    }
}
