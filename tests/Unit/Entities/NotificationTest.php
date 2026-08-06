<?php

use EwertonDaniel\Bitfinex\Entities\Notification;

test('it maps the envelope by index, placeholder included', function () {
    $notification = new Notification([
        1568124312000,
        'oc-req',
        42,
        'PLACEHOLDER',
        ['data'],
        7,
        'SUCCESS',
        'Submitted for cancellation.',
    ]);

    expect($notification->type)->toBe('oc-req')
        ->and($notification->messageId)->toBe(42)
        ->and($notification->data)->toBe(['data'])
        ->and($notification->code)->toBe(7)
        ->and($notification->status)->toBe('SUCCESS')
        ->and($notification->text)->toBe('Submitted for cancellation.')
        ->and($notification->raw)->toHaveCount(8);
});

test('MTS is normalized by magnitude, since the unit differs per endpoint', function (int|float $mts, string $expected) {
    // submit-order documents seconds, update and cancel document milliseconds,
    // and the notifications nested in order/multi carry fractional seconds.
    $notification = new Notification([$mts, 'on-req', null, null, null, null, 'SUCCESS', '']);

    expect($notification->mts->format('Y-m-d H:i:s.v'))->toBe($expected);
})->with([
    'seconds' => [1568124312, '2019-09-10 14:05:12.000'],
    'milliseconds' => [1568124312000, '2019-09-10 14:05:12.000'],
    'fractional seconds' => [1569347312.977, '2019-09-24 17:48:32.977'],
]);

test('a missing timestamp is null rather than the epoch', function () {
    expect((new Notification([]))->mts)->toBeNull();
});

test('only SUCCESS counts as success', function (?string $status, bool $expected) {
    $notification = new Notification([null, 'on-req', null, null, null, null, $status, '']);

    expect($notification->isSuccess())->toBe($expected);
})->with([
    'success' => ['SUCCESS', true],
    'error' => ['ERROR', false],
    'failure' => ['FAILURE', false],
    'a status the API adds later' => ['PARTIALLY_FILLED', false],
    'no status at all' => [null, false],
]);

test('rows returns the list entries and nothing else', function (mixed $data, int $expected) {
    $notification = new Notification([null, 'on-req', null, null, $data, null, 'SUCCESS', '']);

    expect($notification->rows())->toHaveCount($expected);
})->with([
    'a list of rows' => [[[1], [2]], 2],
    'a flat record' => [[1234567, null, 'tBTCUSD'], 0],
    'an empty list' => [[], 0],
    'no data at all' => [null, 0],
]);
