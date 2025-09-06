<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps funding stats to FundingStat entities', function () {
    $payload = [
        ['fUSD', 1700000000000, 0.0005],
        ['fUSD', 1700000300000, 0.0006],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->fundingStats('fUSD');

    expect($resp->content['items'])
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resp->content['items'][0]->data[0])->toBe('fUSD');
});

