<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Adapters;

class UrlAdapter extends JsonAdapter
{
    protected function getFilePath(): string
    {
        return __DIR__.'/../../resources/urls.json';
    }
}
