<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

class LoginInfo
{
    /** @note Login ID */
    public readonly int $id;

    /** @note Millisecond timestamp of login */
    public readonly Carbon $time;

    /** @note IP address of login */
    public readonly string $ip;

    /** @note Object with extra information */
    public readonly array $extraInfo;

    public function __construct(array $data)
    {
        $this->id = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->time = GetThis::ifTrueOrFallback(isset($data[2]), fn () => Carbon::createFromTimestampMs($data[2]));
        $this->ip = GetThis::ifTrueOrFallback(isset($data[4]), fn () => $data[4]);
        $this->extraInfo = GetThis::ifTrueOrFallback(isset($data[7]), fn () => json_decode($data[7], true), []);
    }
}
