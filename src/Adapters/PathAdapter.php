<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

/**
 * Class PathAdapter
 *
 * Extends the JsonAdapter to handle transformation of a JSON file containing API path mappings into an associative array.
 * This adapter specifies the file path to the `paths.json` resource located within the `resources` directory of the project.
 *
 * Leveraging the base functionality of JsonAdapter, this class ensures that the `paths.json` file exists,
 * reads its contents, and decodes it into a PHP array for further use in the application.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class PathAdapter extends JsonAdapter
{
    /**
     * Specifies the file path for the `paths.json` resource.
     *
     * This method defines the location of the JSON file containing API path mappings
     * relative to the current directory.
     *
     * @return string The file path to the `paths.json` resource.
     */
    protected function getFilePath(): string
    {
        return __DIR__ . '/../../resources/paths.json';
    }
}

