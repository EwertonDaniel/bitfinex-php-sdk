<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

class FundingOffer
{
    /** @var array<int,mixed> */
    public readonly array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}

