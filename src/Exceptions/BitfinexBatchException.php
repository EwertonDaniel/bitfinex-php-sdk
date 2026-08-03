<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Throwable;

/**
 * Class BitfinexBatchException
 *
 * Raised when `/auth/w/order/multi` reports at least one rejected sub-operation.
 *
 * The endpoint nests a full notification per operation inside the DATA of the
 * outer notification, each with its own STATUS and TEXT, and the outer STATUS can
 * read `SUCCESS` while an inner one reads `ERROR`. Checking only the outer status
 * reports a partially failed batch as a clean success.
 *
 * The batch is not rolled back, so the accepted operations are live on the
 * exchange. Both lists therefore travel with the exception: `$operations` holds
 * every sub-operation in the order the API returned them, and `$failures` holds
 * the subset that was rejected.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-order-multi
 */
class BitfinexBatchException extends BitfinexApiException
{
    /**
     * @param  Notification  $notification  The outer envelope.
     * @param  list<Notification>  $operations  Every sub-operation, accepted and rejected alike.
     * @param  list<Notification>  $failures  The rejected subset.
     * @param  int  $httpStatus  HTTP status that carried the batch, normally 200.
     */
    public function __construct(
        public readonly Notification $notification,
        public readonly array $operations,
        public readonly array $failures,
        int $httpStatus = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: self::describe($operations, $failures),
            httpStatus: $httpStatus,
            body: $notification->raw,
            previous: $previous
        );
    }

    /**
     * The sub-operations the exchange accepted. They were not rolled back.
     *
     * @return list<Notification>
     */
    final public function succeeded(): array
    {
        return array_values(array_filter($this->operations, fn (Notification $op) => $op->isSuccess()));
    }

    /**
     * @param  list<Notification>  $operations
     * @param  list<Notification>  $failures
     */
    private static function describe(array $operations, array $failures): string
    {
        $reasons = array_values(array_filter(array_map(
            fn (Notification $op) => trim((string) $op->text),
            $failures
        )));

        return sprintf(
            'The Bitfinex API rejected %d of %d operations in the batch: %s',
            count($failures),
            count($operations),
            GetThis::ifTrueOrFallback(
                boolean: $reasons !== [],
                callback: fn () => implode(' | ', $reasons),
                fallback: '(no reason given)'
            )
        );
    }
}
