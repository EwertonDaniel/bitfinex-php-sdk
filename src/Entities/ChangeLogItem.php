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
 * Provides structured data for the timestamp, log message, IP address, and user agent.
 *
 * @author  Ewerton
 *
 * @contact contact@ewertondaniel.work
 */
class ChangeLogItem
{
    /** @note Millisecond timestamp of change */
    public readonly Carbon $createdAt;

    /** @note Log entry describing the change */
    public readonly string $log;

    /** @note IP address for the logged change */
    public readonly ?string $ip;

    /** @note Browser or device information associated with the logged change */
    public readonly ?array $userAgent;

    /**
     * Constructs a ChangeLogItem entity from the provided data.
     *
     * @param  array  $data  Array containing changelog details from the Bitfinex API response.
     */
    public function __construct(array $data)
    {
        $this->createdAt = Carbon::createFromTimestampMs($data[0]);
        $this->log = $data[2];
        $this->ip = GetThis::ifTrueOrFallback(isset($data[5]), fn () => $data[5]);
        $this->userAgent = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (new Parser($data[6]))->toArray());
    }
}
