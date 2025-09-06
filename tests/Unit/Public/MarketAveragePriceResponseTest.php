<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps market average price result to entity', function () {
    $payload = ['avgPrice' => 35123.45, 'amount' => '0.5', 'symbol' => 'tXMRUSD'];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->marketAveragePrice();

    expect($resp->content['result'])
        ->toHaveProperty('result')
        ->and($resp->content['result']->result['avgPrice'])->toBe(35123.45);
});

