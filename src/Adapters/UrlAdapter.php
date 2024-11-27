<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

/**
 * Class UrlAdapter
 *
 * Extends the JsonAdapter to provide functionality for transforming a JSON file containing URL mappings
 * into an associative array. This adapter specifies the file path to the `urls.json` resource located
 * within the `resources` directory of the project structure.
 *
 * By utilizing the base functionality of JsonAdapter, this class ensures that the `urls.json` file exists,
 * reads its content, and decodes it into a PHP array for use in the application.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class UrlAdapter extends JsonAdapter
{
    /**
     * Specifies the file path for the `urls.json` resource.
     *
     * This method defines the location of the JSON file containing URL mappings
     * relative to the current directory.
     *
     * @return string The file path to the `urls.json` resource.
     */
    protected function getFilePath(): string
    {
        return __DIR__.'/../../resources/urls.json';
    }
}
