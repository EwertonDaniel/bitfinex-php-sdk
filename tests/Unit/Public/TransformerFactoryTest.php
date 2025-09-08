<?php

use EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

test('TransformerFactory resolves transformers by name', function () {
    $factory = app(TransformerFactory::class);
    $names = [
        'platformStatus', 'ticker', 'tickers', 'tickerHistory', 'foreignExchangeRate',
        'trades', 'book', 'stats', 'candles', 'derivativesStatus', 'liquidations',
        'leaderboards', 'fundingStats', 'marketAveragePrice',
    ];

    foreach ($names as $name) {
        $t = $factory->make($name);
        expect($t)->toBeInstanceOf(PublicTransformer::class);
    }
});
