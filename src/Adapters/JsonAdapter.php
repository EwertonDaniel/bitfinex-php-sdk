<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexFileNotFoundException;
use GuzzleHttp\Utils;

/**
 * Class JsonAdapter
 *
 * Provides a base class for JSON file adapters, enabling structured transformation of JSON files into PHP arrays.
 * This class includes functionality to retrieve the file path and ensure the existence of the JSON file.
 *
 * The `transform` method decodes the JSON file content into an associative array for further processing.
 * Concrete implementations must define the `getFilePath` method to specify the location of the JSON file.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
abstract class JsonAdapter
{
    protected string $file;

    public function __construct()
    {
        $this->file = $this->getFilePath();
    }

    /**
     * Returns the file path for the JSON file to be processed.
     *
     * Concrete implementations should provide the path to the JSON file
     * that needs to be transformed.
     *
     * @return string The file path of the JSON file.
     */
    abstract protected function getFilePath(): string;

    /**
     * Transforms the JSON file into an associative array.
     *
     * Ensures the file exists, reads its contents, and decodes the JSON into a PHP array.
     * Throws an exception if the file does not exist.
     *
     * @throws BitfinexFileNotFoundException If the file is not found at the specified path.
     *
     * @return array The associative array representation of the JSON file contents.
     */
    public function transform(): array
    {
        if (! file_exists($this->file)) {
            throw new BitfinexFileNotFoundException($this->file);
        }

        $contents = file_get_contents($this->file);

        return Utils::jsonDecode($contents, true);
    }
}

