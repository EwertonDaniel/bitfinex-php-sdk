<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class CountryAndRegion
 *
 * Represents a combination of country code and region extracted from Bitfinex API data.
 * Provides structured access to:
 * - The country code (e.g., 'US', 'BR').
 * - The region, parsed from the API response, ensuring proper formatting and handling of
 *   region strings with underscores (e.g., 'State_Name').
 *
 * This entity simplifies parsing and usage of geographical information in the Bitfinex platform.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class CountryAndRegion
{
    /** Country code (e.g., 'US', 'BR', 'PT'). */
    public readonly ?string $countryCode;

    /** Region (e.g., 'California', 'Paso Fundo','Madeira'). */
    public readonly ?string $region;

    /**
     * Constructs a CountryAndRegion entity with data from the Bitfinex API.
     *
     * @param  array  $data  Array containing:
     *                       - [0] => countryCode (string|null): The country code.
     *                       - [1] => region (string|null): The region string, possibly containing an underscore.
     */
    public function __construct(array $data)
    {
        $this->countryCode = GetThis::ifTrueOrFallback(
            boolean: isset($data[0]),
            fallback: fn () => $data[0]
        );

        $this->region = GetThis::ifTrueOrFallback(
            boolean: isset($data[1]),
            callback: fn () => GetThis::ifTrueOrFallback(
                boolean: str_contains($data[1], '_'),
                callback: fn () => explode('_', $data[1])[1],
                fallback: fn () => $data[1]
            ),
        );
    }
}
