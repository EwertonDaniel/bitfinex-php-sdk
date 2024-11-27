<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

/**
 * Class Stat
 *
 * Represents a single item in the Stats response from the Bitfinex API.
 * Encapsulates the timestamp and total amount information in a structured format.
 *
 * Key Features:
 * - Handles millisecond epoch timestamps.
 * - Converts the entity to an array for easy integration with other systems.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Stat implements Arrayable
{
    /** Millisecond epoch timestamp. */
    public readonly ?Carbon $datetime;

    /** Total amount. */
    public readonly ?float $amount;

    /**
     * Constructs a Stat entity with the provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Timestamp in milliseconds (optional).
     *                       - [1]: Total amount (optional).
     */
    public function __construct(array $data = [])
    {
        $this->datetime = GetThis::ifTrueOrFallback(
            boolean: isset($data[0]),
            callback: fn () => Carbon::createFromTimestampMs($data[0])
        );
        $this->amount = GetThis::ifTrueOrFallback(
            boolean: isset($data[1]),
            callback: fn () => (float) $data[1]
        );
    }

    /**
     * Converts the Stat entity to an associative array.
     *
     * @return array Associative array representation of the Stat entity.
     */
    public function toArray(): array
    {
        return [
            'datetime' => $this->datetime?->toISOString(),
            'amount' => $this->amount,
        ];
    }
}
