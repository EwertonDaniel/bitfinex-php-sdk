<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Facades;

use Illuminate\Support\Facades\Facade;

class Bitfinex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bitfinex';
    }
}
