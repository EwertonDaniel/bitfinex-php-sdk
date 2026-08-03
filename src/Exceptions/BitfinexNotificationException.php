<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Throwable;

/**
 * Class BitfinexNotificationException
 *
 * Raised when a write endpoint answers HTTP 200 with a notification whose STATUS
 * is not `SUCCESS`.
 *
 * This is the in-band failure path: the request reached the exchange, the
 * exchange answered, and the answer says the operation did not happen. Parsing it
 * as a success used to hand the caller an `Order` built from whatever the API put
 * in DATA, with the reason in TEXT silently dropped.
 *
 * DATA is still carried, through `$notification->data`: on a rejected update or
 * cancel it holds the order as it stands on the exchange, which is exactly what a
 * caller needs to decide what to do next.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-submit-order
 */
class BitfinexNotificationException extends BitfinexApiException
{
    /**
     * @param  Notification  $notification  The envelope the API answered with, DATA included.
     * @param  int  $httpStatus  HTTP status that carried the notification, normally 200.
     */
    public function __construct(
        public readonly Notification $notification,
        int $httpStatus = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: self::describe($notification),
            apiCode: $notification->code,
            httpStatus: $httpStatus,
            body: $notification->raw,
            previous: $previous
        );
    }

    /**
     * Builds a message out of the notification, using the API's own words.
     */
    private static function describe(Notification $notification): string
    {
        $operation = $notification->type ?? 'the operation';
        $status = $notification->status ?? 'no status at all';
        $text = trim((string) $notification->text);

        $reason = GetThis::ifTrueOrFallback(
            boolean: $text !== '',
            callback: fn () => ": $text",
            fallback: '.'
        );

        return "The Bitfinex API answered $operation with status $status$reason";
    }
}
