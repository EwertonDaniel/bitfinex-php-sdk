<?php

use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Entities\Order;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexBatchException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexOrderRejectedException;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use GuzzleHttp\Psr7\Response;

/** A full order row, [0]-[31], as the write endpoints return it. */
function orderRow(int $id, string $status = 'ACTIVE'): array
{
    return [
        $id, null, 1568124312, 'tBTCUSD', 1568124312000, 1568124312000,
        0.001, 0.001, 'EXCHANGE LIMIT', null, null, null,
        0, $status, null, null,
        15000.0, 0.0, 0.0, 0.0,
        null, null, null, 0, 0, null, null, null,
        'API>BFX', null, null, [],
    ];
}

/** A notification envelope, [0]-[7]. */
function envelope(mixed $data, string $status = 'SUCCESS', string $type = 'on-req', string $text = 'ok', mixed $mts = 1568124312): array
{
    return [$mts, $type, null, null, $data, null, $status, $text];
}

function respond(array $payload): AuthenticatedBitfinexResponse
{
    return new AuthenticatedBitfinexResponse(new Response(200, [], json_encode($payload)));
}

test('submit reads DATA as a list of orders', function () {
    // DATA[0] is a row here. On update and cancel the same index holds an order
    // id, which is why the shape is decided by the endpoint, not by the payload.
    $content = respond(envelope([orderRow(1234567)]))->submitOrder()->content;

    expect($content['orders'])->toHaveCount(1)
        ->and($content['orders'][0])->toBeInstanceOf(Order::class)
        ->and($content['order'])->toBeInstanceOf(Order::class)
        ->and($content['order']->id)->toBe(1234567)
        ->and($content['notification'])->toBeInstanceOf(Notification::class);
});

test('update and cancel read DATA as a single flat order', function (string $type) {
    $content = respond(envelope(orderRow(1234567), type: $type, mts: 1568124312000))
        ->orderNotification()
        ->content;

    expect($content['order'])->toBeInstanceOf(Order::class)
        ->and($content['order']->id)->toBe(1234567)
        ->and($content['notification']->type)->toBe($type);
})->with(['ou-req', 'oc-req']);

test('order is the same type on submit, update and cancel', function () {
    $submitted = respond(envelope([orderRow(1)]))->submitOrder()->content['order'];
    $cancelled = respond(envelope(orderRow(1), type: 'oc-req'))->orderNotification()->content['order'];

    expect($submitted)->toBeInstanceOf(Order::class)
        ->and($cancelled)->toBeInstanceOf(Order::class);
});

test('an accepted submission that left no order raises', function (string $status) {
    // HTTP 200, envelope STATUS says SUCCESS, and the order says otherwise.
    $envelope = envelope([orderRow(1234567, $status)], 'SUCCESS', 'on-req', 'Submitting exchange limit buy order.');

    try {
        respond($envelope)->submitOrder();
        $this->fail("an order left in status $status was reported as accepted");
    } catch (BitfinexOrderRejectedException $e) {
        expect($e->rejected)->toHaveCount(1)
            ->and($e->accepted())->toBe([])
            ->and($e->getMessage())->toContain($status)
            ->and($e->notification->status)->toBe('SUCCESS')
            ->and($e->httpStatus)->toBe(200);
    }
})->with([
    'insufficient balance U1' => ['INSUFFICIENT BALANCE (U1)'],
    'post only cancelled' => ['POSTONLY CANCELED'],
    'fill or kill cancelled' => ['FILLORKILL CANCELED'],
]);

test('a submission that filled, even partially, is not a rejection', function (string $status) {
    $content = respond(envelope([orderRow(1234567, $status)]))->submitOrder()->content;

    expect($content['order']->status)->toBe($status);
})->with([
    'executed' => ['EXECUTED @ 15000.0(0.001)'],
    'partially filled' => ['PARTIALLY FILLED @ 15000.0(0.0005)'],
    // G1 means the balance ran short on slippage after the order was placed, and
    // it filled for the maximum affordable amount. U1 is the one that leaves
    // nothing; lumping the two together would raise on a real fill.
    'insufficient balance G1' => ['INSUFFICIENT BALANCE (G1)'],
]);

test('a cancelled order does not raise on the cancel endpoint', function () {
    // CANCELED is the intended outcome here, so the submit check must not apply.
    $content = respond(envelope(orderRow(1234567, 'CANCELED'), 'SUCCESS', 'oc-req'))
        ->orderNotification()
        ->content;

    expect($content['order']->status)->toBe('CANCELED')
        ->and($content['order']->wasRejected())->toBeTrue();
});

test('rejected statuses in history are data, not failures', function () {
    // A history call returns whatever happened, cancellations included.
    $content = respond([orderRow(1, 'INSUFFICIENT BALANCE (U1)'), orderRow(2, 'ACTIVE')])
        ->ordersHistory()
        ->content;

    expect($content['orders'])->toHaveCount(2)
        ->and($content['orders'][0]->wasRejected())->toBeTrue();
});

test('an empty DATA yields no order instead of fataling', function () {
    // Building an Order out of [] used to assign null to a non-nullable int.
    $content = respond(envelope([]))->submitOrder()->content;

    expect($content['order'])->toBeNull()
        ->and($content['orders'])->toBe([]);
});

test('a status other than SUCCESS raises, whatever the status word is', function (string $status) {
    respond(envelope([orderRow(1)], status: $status, text: 'Invalid order.'))->submitOrder();
})->with(['ERROR', 'FAILURE', 'SOMETHING_THE_API_ADDS_LATER'])
    ->throws(BitfinexNotificationException::class);

test('a rejection carries the API text and keeps DATA reachable', function () {
    // On a refused cancel, DATA holds the order as it stands on the exchange.
    $rejected = envelope(orderRow(1234567, 'EXECUTED @ 15000.0(0.001)'), 'ERROR', 'oc-req', 'Order not found.');

    try {
        respond($rejected)->orderNotification();
        $this->fail('the rejected notification was accepted as a success');
    } catch (BitfinexNotificationException $e) {
        expect($e->getMessage())->toContain('Order not found.')
            ->and($e->notification->status)->toBe('ERROR')
            ->and($e->notification->data[0])->toBe(1234567)
            ->and($e->notification->data[13])->toBe('EXECUTED @ 15000.0(0.001)')
            ->and($e->body[6])->toBe('ERROR')
            ->and($e->httpStatus)->toBe(200);
    }
});

test('order/multi reports a sub-operation failure the outer status hides', function () {
    // The outer envelope says SUCCESS while the second operation says ERROR.
    $batch = respond(envelope([
        envelope([orderRow(111)], 'SUCCESS', 'on-req', 'Submitting 1 order.', 1569347312.977),
        envelope(null, 'ERROR', 'on-req', 'Invalid order: not enough balance.', 1569347312.978),
    ], 'SUCCESS', 'ou-req', 'Submitting 2 orders.'));

    try {
        $batch->orderMultiOp();
        $this->fail('the partially failed batch was reported as a success');
    } catch (BitfinexBatchException $e) {
        expect($e->failures)->toHaveCount(1)
            ->and($e->operations)->toHaveCount(2)
            ->and($e->succeeded())->toHaveCount(1)
            ->and($e->getMessage())->toContain('rejected 1 of 2')
            ->and($e->getMessage())->toContain('not enough balance');
    }
});

test('order/multi passes a wholly successful batch through', function () {
    $content = respond(envelope([
        envelope([orderRow(111)], 'SUCCESS', 'on-req', 'Submitting 1 order.'),
        envelope(orderRow(222), 'SUCCESS', 'oc-req', 'Submitted for cancellation.'),
    ], 'SUCCESS', 'ou-req', 'Submitting 2 orders.'))->orderMultiOp()->content;

    expect($content['operations'])->toHaveCount(2)
        ->and($content['operations'][0])->toBeInstanceOf(Notification::class)
        ->and($content['operations'][1]->isSuccess())->toBeTrue();
});

test('cancel/multi reconciles the ids asked against the ids returned', function () {
    // The endpoint carries one status for the whole batch and says nothing about
    // ids it did not act on, so reconciliation is the only signal available.
    $content = respond(envelope([orderRow(111), orderRow(222)], 'SUCCESS', 'oc_multi-req', 'Submitting 2 order cancellations.'))
        ->orderCancelMulti([111, 222, 333])
        ->content;

    expect($content['cancelledIds'])->toBe([111, 222])
        ->and($content['missingIds'])->toBe([333])
        ->and($content['orders'][0])->toBeInstanceOf(Order::class);
});

test('cancel/multi reports nothing missing when no ids were given', function () {
    $content = respond(envelope([orderRow(111)], 'SUCCESS', 'oc_multi-req'))->orderCancelMulti()->content;

    expect($content['missingIds'])->toBe([]);
});

test('a refused wallet transfer raises instead of returning a status field', function () {
    $refused = envelope(
        [1568124312000, 'exchange', 'margin', null, 'UST', 'UST', null, 10.0],
        'ERROR',
        'acc_tf',
        'Not enough balance in exchange wallet.'
    );

    try {
        respond($refused)->transferBetweenWallets();
        $this->fail('the refused transfer was accepted as a success');
    } catch (BitfinexNotificationException $e) {
        expect($e->getMessage())->toContain('Not enough balance in exchange wallet.');
    }
});

test('a successful transfer keeps exposing the transferred payload', function () {
    $content = respond(envelope([1568124312000, 'exchange', 'margin', null, 'UST', 'UST', null, 10.0], 'SUCCESS', 'acc_tf', 'Success'))
        ->transferBetweenWallets()
        ->content;

    expect($content['status'])->toBe('SUCCESS')
        ->and($content['text'])->toBe('Success')
        ->and($content['transferred'][4])->toBe('UST');
});
