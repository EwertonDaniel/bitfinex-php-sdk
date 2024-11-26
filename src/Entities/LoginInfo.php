<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class LoginInfo
 *
 * Represents login details on the Bitfinex platform.
 * Provides structured data for:
 * - Login ID.
 * - Timestamp of login.
 * - IP address of login.
 * - Additional information associated with the login.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class LoginInfo
{
    /** Login ID. */
    public readonly int $id;

    /** Timestamp of login. */
    public readonly Carbon $time;

    /** IP address of the login. */
    public readonly string $ip;

    /** Extra information associated with the login. */
    public readonly array $extraInfo;

    /**
     * Constructs a LoginInfo entity using provided data.
     *
     * @param array $data Array containing:
     *                       - [0]: Login ID.
     *                       - [2]: Millisecond timestamp of login.
     *                       - [4]: IP address.
     *                       - [7]: Extra information as JSON (optional).
     */
    public function __construct(array $data)
    {
        $this->id = GetThis::ifTrueOrFallback(isset($data[0]), fn() => $data[0]);
        $this->time = GetThis::ifTrueOrFallback(isset($data[2]), fn() => Carbon::createFromTimestampMs($data[2]));
        $this->ip = GetThis::ifTrueOrFallback(isset($data[4]), fn() => $data[4]);
        $this->extraInfo = GetThis::ifTrueOrFallback(isset($data[7]), fn() => json_decode($data[7], true), []);
    }
}
