<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class CountryAndRegion
{
    public readonly string $countryCode;

    public readonly string $region;

    public function __construct(array $data)
    {
        $this->countryCode = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->region = GetThis::ifTrueOrFallback(
            isset($data[1]),
            fn () => GetThis::ifTrueOrFallback(
                str_contains($data[1], '_'),
                fn () => explode('_', $data[1])[1],
                fn () => $data[1]
            ),
        );
    }
}
