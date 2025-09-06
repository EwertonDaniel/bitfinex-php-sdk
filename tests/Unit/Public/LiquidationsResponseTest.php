<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps liquidations to Liquidation entities', function () {
    $payload = [
        [123, 1700000000000, 'tBTCUSD', -0.5, 35000.0],
        [124, 1700000300000, 'tETHUSD', -10.0, 2000.0],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->liquidations();

    expect($resp->content['liquidations'])
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resp->content['liquidations'][0]->symbol)->toBe('tBTCUSD');
});

