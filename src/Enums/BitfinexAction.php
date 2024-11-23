<?php

namespace EwertonDaniel\Bitfinex\Enums;

enum BitfinexAction: string
{
    case BUY = 'buy';
    case SELL = 'sell';

    public static function fromValue(float $execAmount): self
    {
        return $execAmount > 0 ? self::BUY : self::SELL;
    }

    final public function isSell(): bool
    {
        return $this === self::SELL;
    }
}
