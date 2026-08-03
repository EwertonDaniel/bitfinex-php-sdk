<?php

use EwertonDaniel\Bitfinex\Entities\Order;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexApiException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexBatchException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexOrderRejectedException;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use GuzzleHttp\Psr7\Response;
use Tests\Support\BitfinexMock;
use Tests\Support\Fixtures;

/** @link https://docs.bitfinex.com/reference/rest-auth-retrieve-orders */
test('Should Retrieve Orders', function (?string $symbol, string $path) {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::orders()]);

    $response = $mock->authenticated()->generateToken()->orders()->retrieve($symbol);

    expect($response)->toBeInstanceOf(BitfinexResponse::class)
        ->and($response->content['orders'])->toHaveCount(2)
        ->and($response->content['orders'][0])->toBeInstanceOf(Order::class)
        ->and($response->content['orders'][0]->id)->toBe(1234567)
        ->and($response->content['orders'][1]->status)->toBe('PARTIALLY FILLED')
        ->and($mock->paths()[1])->toBe($path);
})->with([
    'with symbol' => ['XMRUST', '/v2/auth/r/orders/tXMRUST'],
    'without symbol' => [null, '/v2/auth/r/orders'],
]);

/** @link https://docs.bitfinex.com/reference/rest-auth-submit-order */
test('Should Submit Order', function () {
    // Safe to exercise now: nothing leaves the process.
    $mock = BitfinexMock::queue([
        Fixtures::token(),
        Fixtures::notification([Fixtures::order(1234567)], text: 'Submitting exchange limit buy order for 0.02 XMR.'),
    ]);

    $response = $mock->authenticated()->generateToken(writePermission: true, caps: ['o'])
        ->orders()
        ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY, pair: 'XMRUST', amount: 0.02, price: 140);

    expect($response->content['order'])->toBeInstanceOf(Order::class)
        ->and($response->content['order']->id)->toBe(1234567)
        ->and($response->content['orders'])->toHaveCount(1)
        ->and($response->content['notification']->type)->toBe('on-req')
        // The action decides the sign, and the amount goes on the wire as a string.
        ->and($mock->bodyOf(1))->toMatchArray([
            'type' => 'EXCHANGE LIMIT',
            'symbol' => 'tXMRUST',
            'amount' => '0.02',
            'price' => '140',
        ]);
});

test('a sell order goes out with a negative amount', function () {
    $mock = BitfinexMock::queue([Fixtures::notification([Fixtures::order()])]);

    $mock->authenticated()->orders()
        ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::SELL, pair: 'XMRUST', amount: 0.02, price: 140);

    expect($mock->bodyOf(0)['amount'])->toBe('-0.02');
});

test('a refused order reaches the caller as an exception, not as an Order', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(
            [Fixtures::order(1234567)],
            'ERROR',
            'on-req',
            'Invalid order: not enough exchange balance for 0.02 XMR.'
        ),
    ]);

    try {
        $mock->authenticated()->orders()
            ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY, pair: 'XMRUST', amount: 0.02, price: 140);
        $this->fail('the refused order was accepted as a success');
    } catch (BitfinexNotificationException $e) {
        expect($e->getMessage())->toContain('not enough exchange balance')
            // DATA survives: it is the order as it stands on the exchange.
            ->and($e->notification->data[0][0])->toBe(1234567);
    }
});

test('insufficient balance reaches the caller down both routes', function () {
    // Route one: HTTP 500 with an error envelope. Route two: HTTP 200, a
    // notification saying SUCCESS, and an order that left nothing behind. Same
    // outcome for the caller, so both have to raise.
    $mock = BitfinexMock::queue([
        new Response(500, ['Content-Type' => 'application/json'], json_encode(Fixtures::error(10001, 'Invalid order: not enough exchange balance'))),
        Fixtures::notification([Fixtures::order(1234567, 'INSUFFICIENT BALANCE (U1)')]),
    ]);

    $orders = $mock->authenticated()->orders();

    $submit = fn () => $orders->submit(
        type: OrderType::EXCHANGE_LIMIT,
        action: BitfinexAction::BUY,
        pair: 'XMRUST',
        amount: 0.02,
        price: 140
    );

    expect($submit)->toThrow(BitfinexApiException::class);

    try {
        $submit();
        $this->fail('the accepted-then-rejected order was reported as a success');
    } catch (BitfinexOrderRejectedException $e) {
        expect($e->rejected[0]->status)->toBe('INSUFFICIENT BALANCE (U1)')
            ->and($e->httpStatus)->toBe(200)
            ->and($e->notification->status)->toBe('SUCCESS');
    }
});

test('a post-only order that would have matched raises rather than looking placed', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification([Fixtures::order(1234567, 'POSTONLY CANCELED')]),
    ]);

    $mock->authenticated()->orders()->submit(
        type: OrderType::EXCHANGE_LIMIT,
        action: BitfinexAction::BUY,
        pair: 'XMRUST',
        amount: 0.02,
        price: 140,
        flags: 4096
    );
})->throws(BitfinexOrderRejectedException::class, 'POSTONLY CANCELED');

test('a rejection sent as HTTP 500 arrives with the API code', function () {
    $mock = BitfinexMock::queue([
        new Response(500, ['Content-Type' => 'application/json'], json_encode(Fixtures::error())),
    ]);

    try {
        $mock->authenticated()->orders()
            ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY, pair: 'XMRUST', amount: 0.02, price: 140);
        $this->fail('the rejection was accepted as a success');
    } catch (BitfinexApiException $e) {
        expect($e->apiCode)->toBe(BitfinexApiException::ERR_GENERIC)
            ->and($e->httpStatus)->toBe(500);
    }
});

/** @link https://docs.bitfinex.com/reference/rest-auth-update-order */
test('update reads DATA as a single flat order', function () {
    $mock = BitfinexMock::queue([Fixtures::notification(Fixtures::order(1234567), type: 'ou-req')]);

    $response = $mock->authenticated()->orders()->update(id: 1234567, price: 145);

    expect($response->content['order'])->toBeInstanceOf(Order::class)
        ->and($response->content['order']->id)->toBe(1234567)
        ->and($mock->bodyOf(0))->toMatchArray(['id' => 1234567, 'price' => '145']);
});

/** @link https://docs.bitfinex.com/reference/rest-auth-cancel-order */
test('cancel reads DATA as a single flat order', function () {
    $mock = BitfinexMock::queue([Fixtures::notification(Fixtures::order(1234567), type: 'oc-req')]);

    $response = $mock->authenticated()->orders()->cancel(id: 1234567);

    expect($response->content['order'])->toBeInstanceOf(Order::class)
        ->and($response->content['order']->id)->toBe(1234567)
        ->and($mock->bodyOf(0))->toBe(['id' => 1234567]);
});

/** @link https://docs.bitfinex.com/reference/rest-auth-order-multi */
test('a partially failed batch does not pass as a success', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification([
            Fixtures::notification([Fixtures::order(111)], 'SUCCESS', 'on-req', 'Submitting 1 order.', 1754006400.977),
            Fixtures::notification(null, 'ERROR', 'on-req', 'Invalid order: not enough balance.', 1754006400.978),
        ], 'SUCCESS', 'ou-req', 'Submitting 2 orders.'),
    ]);

    try {
        $mock->authenticated()->orders()->multi([
            ['type' => 'LIMIT', 'symbol' => 'tXMRUST', 'price' => '140', 'amount' => '0.01'],
            ['type' => 'LIMIT', 'symbol' => 'tXMRUST', 'price' => '150', 'amount' => '99999'],
        ]);
        $this->fail('the partially failed batch was reported as a success');
    } catch (BitfinexBatchException $e) {
        expect($e->failures)->toHaveCount(1)
            ->and($e->succeeded())->toHaveCount(1)
            ->and($e->getMessage())->toContain('rejected 1 of 2');
    }
});

/** @link https://docs.bitfinex.com/reference/rest-auth-cancel-multiple-orders */
test('cancelling several orders reports the ones that did not come back', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification([Fixtures::order(111), Fixtures::order(222)], type: 'oc_multi-req'),
    ]);

    $response = $mock->authenticated()->orders()->cancelMultiple([111, 222, 333]);

    expect($response->content['cancelledIds'])->toBe([111, 222])
        ->and($response->content['missingIds'])->toBe([333])
        ->and($mock->bodyOf(0))->toBe(['id' => [111, 222, 333]]);
});

test('a call that fails leaves nothing behind for the next one', function () {
    // The RequestBuilder is shared across sub-services, so a submit that throws
    // must not leak its body into the cancel that follows.
    $mock = BitfinexMock::queue([
        new Response(500, ['Content-Type' => 'application/json'], json_encode(Fixtures::error())),
        Fixtures::notification(Fixtures::order(999), type: 'oc-req'),
    ]);

    $authenticated = $mock->authenticated();

    try {
        $authenticated->orders()
            ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY, pair: 'XMRUST', amount: 0.02, price: 140);
    } catch (BitfinexApiException) {
        // expected
    }

    $authenticated->orders()->cancel(id: 999);

    expect($mock->bodyOf(1))->toBe(['id' => 999]);
});
