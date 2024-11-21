<?php

namespace EwertonDaniel\Bitfinex\Enums;

enum OrderAction: string
{
    case BUY = 'buy';
    case SELL = 'sell';

    final public function isSell(): bool
    {
        return $this === self::SELL;
    }
}
