<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class PairInfo
{
    /** Trading pair symbol (e.g., tBTCUSD). */
    public readonly string $pair;
    /** First info block for the pair (formerly block1). */
    public readonly PairInfoBlock $one;
    /** Second info block for the pair (formerly block2). */
    public readonly PairInfoBlock $two;

    /**
     * Raw payload structure from Bitfinex: [PAIR, [..block1..], [..block2..]]
     * Access via properties: $one (block1) and $two (block2).
     */
    public function __construct(array $data)
    {
        $this->pair = (string) $data[0];
        $this->one = new PairInfoBlock(GetThis::ifTrueOrFallback(isset($data[1]) && is_array($data[1]), fn () => $data[1], []));
        $this->two = new PairInfoBlock(GetThis::ifTrueOrFallback(isset($data[2]) && is_array($data[2]), fn () => $data[2], []));
    }
}
