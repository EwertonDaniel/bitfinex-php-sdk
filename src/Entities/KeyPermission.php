<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class KeyPermission
{
    /** @note API scope (e.g., 'account', 'orders', 'wallets') */
    public readonly string $scope;

    /** @note Read permission (true = allowed, false = not allowed) */
    public readonly bool $read;

    /** @note Write permission (true = allowed, false = not allowed) */
    public readonly bool $write;

    public function __construct(array $data)
    {
        $this->scope = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->read = GetThis::ifTrueOrFallback(isset($data[1]), fn () => $data[1] === 1, false);
        $this->write = GetThis::ifTrueOrFallback(isset($data[2]), fn () => $data[2] === 1, false);
    }
}
