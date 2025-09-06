<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class PlatformStatus
 *
 * Represents the operational status of the Bitfinex platform.
 * API returns an array like [1] (operative) or [0] (maintenance).
 * This entity maps the numeric flag to a readable string: "operative" or "maintenance".
 *
 * Key Features:
 * - Encapsulates the status of the platform.
 * - Defaults to "operative" if no valid status is provided.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class PlatformStatus
{
    /** Current status of the platform (e.g., "operative", "maintenance"). */
    public readonly string $status;

    /**
     * Constructs a PlatformStatus entity.
     *
     * @param  array  $data  Array containing the platform status flag:
     *                       - [0]: int 1 (operative) or 0 (maintenance).
     */
    public function __construct(array $data)
    {
        $flag = (int) ($data[0] ?? 0);
        $this->status = GetThis::ifTrueOrFallback(
            boolean: $flag === 1,
            callback: 'operative',
            fallback: 'maintenance'
        );
    }
}
