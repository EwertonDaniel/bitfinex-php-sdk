<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class Candle
 *
 * Represents a single OHLCV candle from Bitfinex.
 * Structure: [ MTS, OPEN, CLOSE, HIGH, LOW, VOLUME ]
 */
class Candle
{
    public readonly int $mts;
    public readonly float $open;
    public readonly float $close;
    public readonly float $high;
    public readonly float $low;
    public readonly float $volume;

    public function __construct(array $data)
    {
        $this->mts = (int) $data[0];
        $this->open = (float) $data[1];
        $this->close = (float) $data[2];
        $this->high = (float) $data[3];
        $this->low = (float) $data[4];
        $this->volume = (float) $data[5];
    }
}

