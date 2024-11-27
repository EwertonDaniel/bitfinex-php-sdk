<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class KeyPermission
 *
 * Represents API key permissions on the Bitfinex platform.
 * Provides structured data for:
 * - Scope of the key (e.g., 'account', 'orders').
 * - Read and write access permissions.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class KeyPermission
{
    /** API scope (e.g., 'account', 'orders'). */
    public readonly string $scope;

    /** Read permission (true if allowed). */
    public readonly bool $read;

    /** Write permission (true if allowed). */
    public readonly bool $write;

    /**
     * Constructs a KeyPermission entity using provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Scope (string).
     *                       - [1]: Read permission (1 for allowed, 0 for denied).
     *                       - [2]: Write permission (1 for allowed, 0 for denied).
     */
    public function __construct(array $data)
    {
        $this->scope = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->read = GetThis::ifTrueOrFallback(isset($data[1]), fn () => $data[1] === 1, false);
        $this->write = GetThis::ifTrueOrFallback(isset($data[2]), fn () => $data[2] === 1, false);
    }
}
