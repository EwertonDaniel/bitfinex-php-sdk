<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use Carbon\Carbon;

class DateToTimestamp
{
    /**
     * Converts a date (string or Carbon) to a timestamp.
     *
     * @param  string|Carbon|int| null  $date  The date to be converted.
     * @return int|null The corresponding timestamp, or null if the input is null.
     */
    final public static function convert(string|Carbon|int|null $date): ?int
    {
        if ($date instanceof Carbon) {
            $ms = $date->getTimestampMs();
        } elseif (is_string($date)) {
            $ms = Carbon::parse($date)->getTimestampMs();
        } else {
            $ms = $date;
        }

        return $ms;
    }
}
