<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class TwoFactorAuthModes
 *
 * Represents the Two-Factor Authentication (2FA) modes enabled for a Bitfinex account.
 * Provides details on whether the following modes are enabled:
 * - Universal Second Factor (U2F).
 * - One-Time Password (OTP).
 *
 * Key Features:
 * - Checks for the presence of supported 2FA modes in the provided data.
 * - Defaults to `false` if the mode is not present.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class TwoFactorAuthModes
{
    /** Indicates if Universal Second Factor (U2F) is enabled. */
    public readonly bool $universalSecondFactor;

    /** Indicates if One-Time Password (OTP) is enabled. */
    public readonly bool $oneTimePassword;

    /**
     * Constructs a TwoFactorAuthModes entity.
     *
     * @param  array|null  $data  Array containing enabled 2FA modes:
     *                            - e.g., ['u2f', 'otp'] or null if no modes are enabled.
     */
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
