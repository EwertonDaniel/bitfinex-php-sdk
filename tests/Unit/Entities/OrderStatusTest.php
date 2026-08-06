<?php

use EwertonDaniel\Bitfinex\Entities\Order;

/**
 * The ORDER_STATUS vocabulary, taken verbatim from the reference glossary on
 * 2026-08-03. The composed forms matter: the status is not a keyword, it carries
 * the price and amount inline, and some values name the prior state.
 *
 * @link https://docs.bitfinex.com/docs/abbreviations-glossary
 */
function orderWithStatus(string $status): Order
{
    $row = array_fill(0, 32, null);

    $row[0] = 1234567;
    $row[2] = 1568124312;
    $row[3] = 'tXMRUST';
    $row[4] = 1754006400000;
    $row[5] = 1754006400000;
    $row[8] = 'EXCHANGE LIMIT';
    $row[13] = $status;

    return new Order($row);
}

test('an order in the book is active and not rejected', function () {
    $order = orderWithStatus('ACTIVE');

    expect($order->isActive())->toBeTrue()
        ->and($order->wasFilled())->toBeFalse()
        ->and($order->wasRejected())->toBeFalse();
});

test('an executed order counts as filled, price and amount inline', function (string $status) {
    $order = orderWithStatus($status);

    expect($order->wasFilled())->toBeTrue()
        ->and($order->wasRejected())->toBeFalse();
})->with([
    'fully executed' => ['EXECUTED @ 140.0(0.02)'],
    'partially filled' => ['PARTIALLY FILLED @ 140.0(0.01)'],
    'forced execution' => ['FORCED EXECUTED @ 140.0(0.02)'],
    'position close' => ['EXECUTED @ 140.0(0.02) was ACTIVE (note:POSCLOSE)'],
    // The balance was there when the order was placed; it filled for as much as
    // it could afford. The caller is holding something.
    'insufficient balance G1' => ['INSUFFICIENT BALANCE (G1)'],
    'book slip after a partial fill' => ['RSN_BOOK_SLIP was: PARTIALLY FILLED @ 140.0(0.01)'],
    'dust closed by a market order' => ['RSN_DUST was: ACTIVE (note: POSCLOSE)'],
]);

test('an order that left nothing behind is rejected', function (string $status) {
    $order = orderWithStatus($status);

    expect($order->wasRejected())->toBeTrue()
        ->and($order->wasFilled())->toBeFalse()
        ->and($order->isActive())->toBeFalse();
})->with([
    // The one the checkpoint was after: accepted under HTTP 200, then nothing.
    'insufficient balance U1' => ['INSUFFICIENT BALANCE (U1)'],
    'post only would have matched' => ['POSTONLY CANCELED'],
    'fill or kill could not fill' => ['FILLORKILL CANCELED'],
    'immediate or cancel' => ['IOC CANCELED'],
    'reduce only would increase' => ['RSN_POS_REDUCE_INCR'],
    'reduce only would flip' => ['RSN_POS_REDUCE_FLIP'],
    'reduce only without a position' => ['RSN_POS_NOTFOUND'],
    'book paused' => ['RSN_PAUSE'],
    'plainly cancelled' => ['CANCELED'],
]);

test('the status is read case insensitively and untrimmed', function () {
    expect(orderWithStatus('  active ')->isActive())->toBeTrue()
        ->and(orderWithStatus('executed @ 140.0(0.02)')->wasFilled())->toBeTrue();
});
