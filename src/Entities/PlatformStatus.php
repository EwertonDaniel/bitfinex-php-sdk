<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class PlatformStatus
 *
 * Represents the operational status of the Bitfinex platform.
 * Provides structured access to the platform's current state, which can be
 * either "operative" or "maintenance."
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
     * @param  array  $data  Array containing the platform status:
     *                       - [0]: Platform status (string).
     */
    public function __construct(array $data)
    {
        $this->status = GetThis::ifTrueOrFallback(
            boolean: $data[0],
            callback: 'operative',
            fallback: 'maintenance'
        );
    }
}
