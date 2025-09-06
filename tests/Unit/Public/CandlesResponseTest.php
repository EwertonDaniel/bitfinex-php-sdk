<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps candles to Candle entities', function () {
    $body = json_encode([
        [1700000000000, 100.0, 110.0, 115.0, 95.0, 123.45],
        [1700000060000, 110.0, 120.0, 125.0, 105.0, 67.89],
    ]);

    $resp = (new PublicBitfinexResponse(new Response(200, [], $body)))->candles('tBTCUSD', '1m', 'hist');

    expect($resp->content['candles'])
        ->toBeArray()
        ->and($resp->content['candles'][0])
        ->toHaveProperty('open')
        ->and($resp->content['candles'][1])
        ->toHaveProperty('volume');
});

