<?php

use EwertonDaniel\Bitfinex\Entities\LeaderboardEntry;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps leaderboards to LeaderboardEntry entities', function () {
    // 13 fields, as v2/rankings/... actually returns. The reference documents
    // only 10, and [8] carries an int where it says PLACEHOLDER.
    $payload = [
        [1785542400000, null, 'user1', 1, null, null, 15504971.51, null, 2, null, null, null, null],
        [1785542400000, null, 'user2', 2, null, null, 67.89, null, 3, 'someHandle', null, null, null],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->leaderboards('pnl', '1D', 'tXMRUSD', 'hist');

    expect($resp->content['items'])
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resp->content['items'][0])->toBeInstanceOf(LeaderboardEntry::class)
        ->and($resp->content['items'][0]->username)->toBe('user1')
        ->and($resp->content['items'][0]->ranking)->toBe(1)
        ->and($resp->content['items'][0]->value)->toBe(15504971.51)
        ->and($resp->content['items'][0]->mts->timestamp)->toBe(1785542400)
        // Documented as a string, null on every live row checked.
        ->and($resp->content['items'][0]->twitterHandle)->toBeNull()
        ->and($resp->content['items'][1]->twitterHandle)->toBe('someHandle');
});

test('the undocumented tail stays reachable through the raw row', function () {
    // [10]-[12] have no documented meaning, so they get no accessor. Dropping
    // them entirely would lose data the API is actually sending.
    $payload = [[1785542400000, null, 'user1', 1, null, null, 1.0, null, 2, null, 'x', 'y', 'z']];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->leaderboards('pnl', '1D', 'tXMRUSD', 'hist');

    expect($resp->content['items'][0]->raw)->toHaveCount(13)
        ->and($resp->content['items'][0]->raw[10])->toBe('x')
        ->and($resp->content['items'][0]->raw[12])->toBe('z');
});
