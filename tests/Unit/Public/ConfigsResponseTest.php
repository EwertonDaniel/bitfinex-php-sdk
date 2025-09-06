<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps conf to ConfigEntry entities and skips missing', function () {
    // Two keys requested; second has no data (null), first has an array
    $payload = [
        ['USD', 'US Dollar'],
        null,
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->configs(['pub:map:currency:sym', 'pub:list:pair:exchange']);

    expect($resp->content['configs'])
        ->toBeArray()
        ->toHaveCount(1)
        ->and($resp->content['configs'][0]->key)->toBe('pub:map:currency:sym');
});

