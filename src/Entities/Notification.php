<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Illuminate\Support\Carbon;

/**
 * Class Notification
 *
 * Envelope the write endpoints of the Bitfinex API answer with. It is the same
 * eight-field positional array everywhere, but the DATA field it carries at [4]
 * has an endpoint-specific shape, so this entity deliberately keeps DATA raw and
 * leaves the interpretation to the response mapper that knows which endpoint was
 * called:
 *
 * - `/auth/w/order/submit`             DATA is a list of orders
 * - `/auth/w/order/cancel/multi`       DATA is a list of orders
 * - `/auth/w/order/update`             DATA is a single, flat order
 * - `/auth/w/order/cancel`             DATA is a single, flat order
 * - `/auth/w/order/multi`              DATA is a list of nested notifications
 * - `/auth/w/withdraw`                 DATA is a single, flat withdrawal record
 * - `/auth/w/funding/offer/submit`     DATA is a single, flat funding offer
 * - `/auth/w/funding/offer/cancel`     DATA is a single, flat funding offer
 * - `/auth/w/funding/auto`             DATA is `[CURRENCY, PERIOD, RATE, THRESHOLD]`
 * - `/auth/w/funding/offer/cancel/all`,
 *   `/auth/w/funding/close`,
 *   `/auth/w/funding/keep`             DATA is null; the outcome lives in TEXT
 *
 * `/auth/w/alert/set` is the confirmed exception: it answers with the alert row
 * itself, not with this envelope.
 *
 * STATUS at [6] is documented as an open set (`SUCCESS`, `ERROR`, `FAILURE`,
 * ...), so anything other than `SUCCESS` is a failure. TEXT at [7] is explicitly
 * free-form and subject to change: carry it for humans, never branch on it.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-submit-order
 */
class Notification
{
    /** Timestamp of the notification, normalized to milliseconds. */
    public readonly ?Carbon $mts;

    /** Notification type, e.g. `on-req`, `ou-req`, `oc-req`, `oc_multi-req`, `acc_tf`. */
    public readonly ?string $type;

    /** Message id; the API leaves it null on most endpoints. */
    public readonly ?int $messageId;

    /** DATA field, kept exactly as the API sent it. Its shape depends on the endpoint. */
    public readonly mixed $data;

    /** Notification code; documented as work in progress and usually null. */
    public readonly ?int $code;

    /** Status of the operation: `SUCCESS`, `ERROR`, `FAILURE`, or anything the API adds later. */
    public readonly ?string $status;

    /** Human readable description of the outcome. Free-form. */
    public readonly ?string $text;

    /** The untouched envelope, kept for diagnostics and for fields the API may add. */
    public readonly array $raw;

    /**
     * Constructs a Notification entity from the envelope of a write endpoint.
     *
     * @param  array  $data  Array containing the notification envelope:
     *                       - [0]: Millisecond or second epoch timestamp.
     *                       - [1]: Notification type.
     *                       - [2]: Message ID (optional).
     *                       - [3]: PLACEHOLDER.
     *                       - [4]: DATA, endpoint specific.
     *                       - [5]: Notification code (optional).
     *                       - [6]: Status.
     *                       - [7]: Text.
     */
    public function __construct(array $data)
    {
        $this->raw = $data;
        $this->mts = self::timestamp($data[0] ?? null);
        $this->type = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (string) $data[1]);
        $this->messageId = GetThis::ifTrueOrFallback(is_numeric($data[2] ?? null), fn () => (int) $data[2]);
        $this->data = $data[4] ?? null;
        $this->code = GetThis::ifTrueOrFallback(is_numeric($data[5] ?? null), fn () => (int) $data[5]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (string) $data[6]);
        $this->text = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (string) $data[7]);
    }

    /**
     * Whether the API reported the operation as successful.
     *
     * Anything other than `SUCCESS` counts as a failure, including a missing
     * status: the documented set is open ended and new values are failures until
     * proven otherwise.
     */
    final public function isSuccess(): bool
    {
        return strtoupper((string) $this->status) === 'SUCCESS';
    }

    /**
     * The rows of DATA, for the endpoints whose DATA is a list of arrays.
     *
     * Returns an empty list when DATA is a single flat record or is absent, so a
     * caller that expects a list never has to guard the shape itself.
     *
     * @return list<array>
     */
    final public function rows(): array
    {
        return GetThis::ifTrueOrFallback(
            boolean: is_array($this->data),
            callback: fn () => array_values(array_filter($this->data, 'is_array')),
            fallback: []
        );
    }

    /**
     * Normalizes the timestamp, whose unit is inconsistent across endpoints.
     *
     * The official reference contradicts itself: `submit-order` documents seconds
     * (a ten digit example), `update` and `cancel` document milliseconds (thirteen
     * digits), and the notifications nested inside `order/multi` carry fractional
     * seconds (`1569347312.977`). Deciding by magnitude covers all three, whereas
     * a fixed divisor is wrong for two of them. The `1e11` threshold sits far above
     * any plausible epoch in seconds and far below any epoch in milliseconds.
     */
    private static function timestamp(mixed $value): ?Carbon
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        $milliseconds = GetThis::ifTrueOrFallback(
            boolean: abs($number) < 1e11,
            callback: fn () => $number * 1000,
            fallback: $number
        );

        return Carbon::createFromTimestampMs((int) round($milliseconds));
    }
}
