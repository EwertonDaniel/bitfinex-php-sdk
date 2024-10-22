<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class PlatformStatus
{
    public readonly string $status;

    public function __construct(array $data)
    {
        $this->status = GetThis::ifTrueOrFallback($data[0], 'operative', 'maintenance');
    }
}
