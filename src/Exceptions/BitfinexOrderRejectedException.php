<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Entities\Order;
use Throwable;

/**
 * Class BitfinexOrderRejectedException
 *
 * Raised when a submitted order was accepted by the endpoint and then left
 * nothing behind.
 *
 * Insufficient balance reaches the caller by two different routes. One is an
 * HTTP 500 carrying `["error", 10001, "..."]`, which `BitfinexApiException`
 * already covers. The other is an HTTP 200 whose notification STATUS reads
 * `SUCCESS` while the order it carries has an ORDER_STATUS of
 * `INSUFFICIENT BALANCE (U1)`. Same outcome, no order, but only the first route
 * used to be visible.
 *
 * The same shape covers `POSTONLY CANCELED`, `FILLORKILL CANCELED`,
 * `IOC CANCELED`, the reduce-only `RSN_POS_*` refusals and `RSN_PAUSE`.
 *
 * A partial fill is not a rejection and does not raise: `INSUFFICIENT BALANCE
 * (G1)` and `RSN_BOOK_SLIP was: PARTIALLY FILLED` both leave the caller holding
 * something, and the amounts are on the `Order`.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/docs/abbreviations-glossary
 */
class BitfinexOrderRejectedException extends BitfinexApiException
{
    /**
     * @param  Notification  $notification  The envelope, whose STATUS said SUCCESS.
     * @param  list<Order>  $orders  Every order the notification carried.
     * @param  list<Order>  $rejected  The subset that left nothing behind.
     * @param  int  $httpStatus  HTTP status that carried it, normally 200.
     */
    public function __construct(
        public readonly Notification $notification,
        public readonly array $orders,
        public readonly array $rejected,
        int $httpStatus = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: self::describe($rejected),
            httpStatus: $httpStatus,
            body: $notification->raw,
            previous: $previous
        );
    }

    /**
     * The orders that did survive, if any. Empty on the common single-order case.
     *
     * @return list<Order>
     */
    final public function accepted(): array
    {
        return array_values(array_filter($this->orders, fn (Order $order) => ! $order->wasRejected()));
    }

    /** @param  list<Order>  $rejected */
    private static function describe(array $rejected): string
    {
        $reasons = array_map(fn (Order $order) => "#$order->id: $order->status", $rejected);

        return 'The Bitfinex API accepted the request and left no order behind: '.implode(' | ', $reasons);
    }
}
