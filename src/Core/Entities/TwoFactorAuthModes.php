<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class TwoFactorAuthModes
{
    public readonly bool $universalSecondFactor;

    public readonly bool $oneTimePassword;

    public function __construct(?array $data)
    {
        $this->universalSecondFactor = GetThis::ifTrueOrFallback(
            boolean: $data,
            callback: fn () => in_array('u2f', $data),
            fallback: false
        );
        $this->oneTimePassword = GetThis::ifTrueOrFallback(
            boolean: $data,
            callback: fn () => in_array('otp', $data),
            fallback: false
        );
    }
}
