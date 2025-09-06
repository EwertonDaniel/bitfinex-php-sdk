<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps derivatives status to DerivativeStatus entities', function () {
    $payload = [
        ['tBTCF0:USD', 1700000000000, null, 35000.5, 34950.25, null, 1000000.0, null, 1700000300000, 0.0123, 1, null, 0.001, null, null, 34980.0, null, null, 12345.67, null, null, null, 0.0001, 0.005],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->derivativesStatus(['tBTCF0:USD']);

    $item = $resp->content['items'][0];

    expect($item->key)->toBe('tBTCF0:USD')
        ->and($item->mts)->toBe(1700000000000)
        ->and($item->derivPrice)->toBeFloat()
        ->and($item->spotPrice)->toBeFloat()
        ->and($item->openInterest)->toBeFloat();
});

