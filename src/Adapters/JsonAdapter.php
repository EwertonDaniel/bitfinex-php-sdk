<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

use Exception;
use GuzzleHttp\Utils;

abstract class JsonAdapter
{
    protected string $file;

    public function __construct()
    {
        $this->file = $this->getFilePath();
    }

    abstract protected function getFilePath(): string;

    /** @throws Exception */
    public function transform(): array
    {
        if (!file_exists($this->file)) {
            throw new Exception("File not found: {$this->file}");
        }

        $contents = file_get_contents($this->file);
        return Utils::jsonDecode($contents, true);
    }
}
