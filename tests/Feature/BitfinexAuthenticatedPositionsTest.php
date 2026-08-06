<?php

use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Entities\Position;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use Tests\Support\BitfinexMock;
use Tests\Support\Fixtures;

/** @link https://docs.bitfinex.com/reference/rest-auth-position-claim */
test('a claimed position maps the single flat position its notification carries', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(Fixtures::positionRow(), type: 'pm-req', text: 'Requesting position claim'),
    ]);

    $response = $mock->authenticated()->positions()->claim(id: 142031891);

    $position = $response->content['position'];

    // The envelope used to be read as a list of positions, so a successful
    // claim mapped each envelope field as a "position".
    expect($position)->toBeInstanceOf(Position::class)
        ->and($position->symbol)->toBe('tBTCUSD')
        ->and($position->positionId)->toBe(142031891)
        ->and($position->amount)->toBe(-0.001)
        ->and($position->basePrice)->toBe(10119.0)
        ->and($response->content['notification'])->toBeInstanceOf(Notification::class)
        ->and($mock->paths()[0])->toBe('/v2/auth/w/position/claim')
        ->and($mock->bodyOf(0))->toMatchArray(['id' => 142031891]);
});

test('a refused position claim raises instead of returning', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'pm-req', 'Position not found.'),
    ]);

    $mock->authenticated()->positions()->claim(id: 999);
})->throws(BitfinexNotificationException::class, 'Position not found.');
