<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use WhichBrowser\Parser;

/**
 * Class ChangeLogItem
 *
 * Represents a single entry in the account changelog retrieved from the Bitfinex API.
 * This entity provides structured details about an account change, including:
 * - The timestamp of the change (in milliseconds).
 * - A log message describing the change.
 * - The associated IP address (if available).
 * - Browser or device information parsed from the user agent string.
 *
 * Useful for auditing account activities and tracking changes performed via the Bitfinex platform.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class ChangeLogItem
{
    /** Date of the change. */
    public readonly Carbon $createdAt;

    /** Log entry describing the change. */
    public readonly string $log;

    /** IP address associated with the logged change. */
    public readonly ?string $ip;

    /** Parsed browser or device information associated with the logged change. */
    public readonly ?array $userAgent;

    /**
     * Constructs a ChangeLogItem entity from the provided data.
     *
     * @param array $data Array containing changelog details from the Bitfinex API response:
     *                       - [0] => createdAt (int): Millisecond timestamp of the change.
     *                       - [2] => log (string): Log message describing the change.
     *                       - [5] => ip (string|null): IP address associated with the change.
     *                       - [6] => userAgent (string|null): User agent string for the change.
     */
    public function __construct(array $data)
    {
        $this->createdAt = Carbon::createFromTimestampMs($data[0]);
        $this->log = $data[2];
        $this->ip = GetThis::ifTrueOrFallback(isset($data[5]), fn() => $data[5]);
        $this->userAgent = GetThis::ifTrueOrFallback(isset($data[6]), fn() => (new Parser($data[6]))->toArray());
    }
}
