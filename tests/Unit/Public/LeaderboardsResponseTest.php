<?php

use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps leaderboards to LeaderboardEntry entities', function () {
    $payload = [
        ['user1', 123.45],
        ['user2', 67.89],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->leaderboards('pnl', '1D', 'tBTCUSD', 'hist');

    expect($resp->content['items'])
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resp->content['items'][0]->data[0])->toBe('user1');
});

