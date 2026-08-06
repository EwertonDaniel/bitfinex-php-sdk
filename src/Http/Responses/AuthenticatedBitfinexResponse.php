<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\Alert;
use EwertonDaniel\Bitfinex\Entities\ChangeLogItem;
use EwertonDaniel\Bitfinex\Entities\DepositAddress;
use EwertonDaniel\Bitfinex\Entities\FundingCredit;
use EwertonDaniel\Bitfinex\Entities\FundingLoan;
use EwertonDaniel\Bitfinex\Entities\FundingOffer;
use EwertonDaniel\Bitfinex\Entities\FundingTrade;
use EwertonDaniel\Bitfinex\Entities\KeyPermission;
use EwertonDaniel\Bitfinex\Entities\LedgerEntry;
use EwertonDaniel\Bitfinex\Entities\LoginInfo;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Entities\Order;
use EwertonDaniel\Bitfinex\Entities\Position;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\Trade;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Entities\Wallet;
use EwertonDaniel\Bitfinex\Entities\Withdrawal;
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexBatchException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexOrderRejectedException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class AuthenticatedBitfinexResponse
 *
 * Handles transformations of responses for authenticated endpoints.
 * Converts raw API response content into structured entities for easier handling.
 *
 * Key Features:
 * - Provides utility methods for transforming raw API responses into strongly-typed entities.
 * - Ensures consistency in handling different authenticated endpoint responses.
 *
 * @author Ewerton
 *
 * @contact contact@ewertondaniel.work
 */
class AuthenticatedBitfinexResponse extends BitfinexResponse
{
    /**
     * @return AuthenticatedBitfinexResponse with content array{token: string}.
     */
    final public function generateToken(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['token' => GetThis::ifTrueOrFallback(isset($content[0]), fn () => $content[0])]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{user: \EwertonDaniel\Bitfinex\Entities\User}.
     */
    final public function userInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['user' => new User($content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{summary: \EwertonDaniel\Bitfinex\Entities\Summary}.
     */
    final public function summary(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['summary' => new Summary($content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{history: list<\EwertonDaniel\Bitfinex\Entities\LoginInfo>}.
     */
    final public function loginHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['history' => array_map(fn ($data) => new LoginInfo($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{permissions: list<\EwertonDaniel\Bitfinex\Entities\KeyPermission>}.
     */
    final public function keyPermissions(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['permissions' => array_map(fn ($data) => new KeyPermission($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{changelog: list<\EwertonDaniel\Bitfinex\Entities\ChangeLogItem>}.
     */
    final public function changelog(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['changelog' => array_map(fn ($data) => new ChangeLogItem($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{address: \EwertonDaniel\Bitfinex\Entities\DepositAddress}.
     */
    final public function depositAddress(BitfinexWalletType $walletType): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['address' => new DepositAddress($walletType, $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{addresses: array{method: string, items: list<array{address: \EwertonDaniel\Bitfinex\Entities\DepositAddress}>}}.
     */
    final public function depositAddressList(string $method): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => [
            'addresses' => [
                'method' => $method,
                'items' => array_map(fn ($data) => [
                    'address' => new DepositAddress(
                        BitfinexWalletType::tryFrom((string) $data[1]) ?? (string) $data[1],
                        [
                            null,
                            null,
                            null,
                            null,
                            [
                                null,
                                $method,
                                null,
                                null,
                                $data[0],
                                null,
                            ],
                            null,
                            null,
                            null,
                        ]
                    ),
                ], $content),
            ],
        ]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{wallets: list<\EwertonDaniel\Bitfinex\Entities\Wallet>}.
     */
    final public function wallets(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['wallets' => array_map(fn ($data) => new Wallet($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{orders: list<\EwertonDaniel\Bitfinex\Entities\Order>}.
     */
    final public function retrieveOrders(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{available: numeric}.
     */
    final public function balanceAvailableForOrdersOffers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['available' => $content[0]]);
    }

    /**
     * Transforms the notification returned by the submit endpoint, whose DATA
     * field holds a list of orders.
     *
     * `order` is the first entry, which is all the endpoint ever returns today;
     * `orders` is the full list, for the day it returns more.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, order: \EwertonDaniel\Bitfinex\Entities\Order|null, orders: list<\EwertonDaniel\Bitfinex\Entities\Order>}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     * @throws BitfinexOrderRejectedException When the envelope reports SUCCESS but the
     *                                        order it carries left nothing behind.
     */
    final public function submitOrder(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            $orders = array_map(fn ($order) => new Order($order), $notification->rows());

            // A submission can be accepted and still leave no order: the envelope
            // says SUCCESS under an HTTP 200 while ORDER_STATUS reads INSUFFICIENT
            // BALANCE (U1), POSTONLY CANCELED or FILLORKILL CANCELED. Only submit
            // is checked: on cancel a CANCELED order is the intended outcome, and
            // on retrieve or history these statuses are ordinary history.
            $rejected = array_values(array_filter($orders, fn (Order $order) => $order->wasRejected()));

            if ($rejected !== []) {
                throw new BitfinexOrderRejectedException($notification, $orders, $rejected, $this->statusCode);
            }

            return [
                'notification' => $notification,
                'order' => $orders[0] ?? null,
                'orders' => $orders,
            ];
        });
    }

    /**
     * Transforms an order notification returned by the update and cancel endpoints,
     * whose DATA field holds a single, flat order rather than a list.
     *
     * The shape is decided by the endpoint that was called, not by inspecting the
     * payload: DATA[0] is an array on submit and an order id on update and cancel,
     * so a shared "unwrap DATA[0]" helper reads the id as a row.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, order: \EwertonDaniel\Bitfinex\Entities\Order|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function orderNotification(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'order' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data) && isset($notification->data[0]),
                    callback: fn () => new Order($notification->data)
                ),
            ];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{orders: list<\EwertonDaniel\Bitfinex\Entities\Order>}.
     */
    final public function ordersHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{symbol: string|null, trades: list<\EwertonDaniel\Bitfinex\Entities\Trade>}.
     */
    final public function orderTrades(?string $symbol = null): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'trades' => array_map(fn ($trade) => new Trade($trade), $content),
            ]
        );
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{symbol: string|null, trades: list<\EwertonDaniel\Bitfinex\Entities\Trade>}.
     */
    final public function tradesHistory(?string $symbol = null): AuthenticatedBitfinexResponse
    {
        return $this->orderTrades($symbol);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{ledgers: list<\EwertonDaniel\Bitfinex\Entities\LedgerEntry>}.
     */
    final public function ledgers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['ledgers' => array_map(fn ($row) => new LedgerEntry($row), $content)]);
    }

    /**
     * Transforms the notification returned by the order/multi endpoint, whose DATA
     * field holds one complete notification per sub-operation.
     *
     * Status is per sub-operation here: the outer STATUS can read SUCCESS while a
     * nested one reads ERROR, so the outer status alone reports a partially failed
     * batch as a clean success.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, operations: list<\EwertonDaniel\Bitfinex\Entities\Notification>}.
     *
     * @throws BitfinexNotificationException When the outer status is not SUCCESS.
     * @throws BitfinexBatchException When any sub-operation was rejected. The accepted
     *                                ones are live on the exchange and travel with it.
     */
    final public function orderMultiOp(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            $operations = array_map(fn ($row) => new Notification($row), $notification->rows());
            $failures = array_values(array_filter($operations, fn (Notification $op) => ! $op->isSuccess()));

            if ($failures !== []) {
                throw new BitfinexBatchException($notification, $operations, $failures, $this->statusCode);
            }

            return [
                'notification' => $notification,
                'operations' => $operations,
            ];
        });
    }

    /**
     * Transforms the notification returned by the cancel/multi endpoint, whose DATA
     * field holds the orders that were actually cancelled.
     *
     * The endpoint reports a single status for the whole batch and says nothing
     * about ids it did not act on, so the only way to detect a partial cancellation
     * is to reconcile what was asked against what came back. An id lands in
     * `missingIds` when the exchange returned no order for it: it had already been
     * filled or cancelled, or it never existed. That is not necessarily an error,
     * which is why it is reported rather than thrown.
     *
     * @param  list<int>  $requestedIds  Ids the caller asked to cancel, for reconciliation.
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, orders: list<\EwertonDaniel\Bitfinex\Entities\Order>, cancelledIds: list<int>, missingIds: list<int>}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function orderCancelMulti(array $requestedIds = []): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($requestedIds) {
            $notification = $this->succeededNotification($content);

            $orders = array_map(fn ($row) => new Order($row), $notification->rows());
            $cancelledIds = array_map(fn (Order $order) => $order->id, $orders);

            return [
                'notification' => $notification,
                'orders' => $orders,
                'cancelledIds' => $cancelledIds,
                'missingIds' => array_values(array_diff($requestedIds, $cancelledIds)),
            ];
        });
    }

    // Funding mappings
    /**
     * @return AuthenticatedBitfinexResponse with content array{offers: list<\EwertonDaniel\Bitfinex\Entities\FundingOffer>}.
     */
    final public function fundingOffers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['offers' => array_map(fn ($row) => new FundingOffer($row), $content)]);
    }

    /**
     * Transforms the notification returned by the funding offer submit endpoint,
     * whose DATA field holds the offer as the exchange recorded it.
     *
     * The envelope used to be handed to `FundingOffer` whole, so every field read
     * the wrong index: `id` got the timestamp, `symbol` got the type string.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, offer: \EwertonDaniel\Bitfinex\Entities\FundingOffer|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function fundingOfferSubmitted(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'offer' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data),
                    callback: fn () => new FundingOffer($notification->data)
                ),
            ];
        });
    }

    /**
     * Transforms the notification returned by the funding offer cancel endpoint,
     * whose DATA field holds the offer as it stands after the cancellation.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, offer: \EwertonDaniel\Bitfinex\Entities\FundingOffer|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function cancelFundingOffer(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'offer' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data),
                    callback: fn () => new FundingOffer($notification->data)
                ),
            ];
        });
    }

    /**
     * Transforms the notification returned by the cancel-all endpoint. Its DATA
     * field is null; the outcome, including how many offers were submitted for
     * cancellation, only exists in the free-form TEXT.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function cancelAllFundingOffers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => [
            'notification' => $this->succeededNotification($content),
        ]);
    }

    /**
     * Transforms the notification returned by the funding close endpoint. Its
     * DATA field is null.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function fundingClose(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => [
            'notification' => $this->succeededNotification($content),
        ]);
    }

    /**
     * Transforms the notification returned by the auto-renew endpoint, whose DATA
     * field holds `[CURRENCY, PERIOD, RATE, THRESHOLD]`.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, autorenew: array|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function fundingAutoRenew(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'autorenew' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data),
                    callback: fn () => $notification->data
                ),
            ];
        });
    }

    /**
     * Transforms the notification returned by the keep-funding endpoint. Its DATA
     * field is null.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function keepFunding(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => [
            'notification' => $this->succeededNotification($content),
        ]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{loans: list<\EwertonDaniel\Bitfinex\Entities\FundingLoan>}.
     */
    final public function fundingLoans(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['loans' => array_map(fn ($row) => new FundingLoan($row), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{credits: list<\EwertonDaniel\Bitfinex\Entities\FundingCredit>}.
     */
    final public function fundingCredits(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['credits' => array_map(fn ($row) => new FundingCredit($row), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{trades: list<\EwertonDaniel\Bitfinex\Entities\FundingTrade>}.
     */
    final public function fundingTrades(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['trades' => array_map(fn ($row) => new FundingTrade($row), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{info: mixed}.
     */
    final public function fundingInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['info' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{movements: list<\EwertonDaniel\Bitfinex\Entities\Movement>}.
     */
    final public function movements(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movements' => array_map(fn ($data) => new Movement($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{movement: \EwertonDaniel\Bitfinex\Entities\Movement}.
     */
    final public function movementInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movement' => new Movement($content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{deposits: list<\EwertonDaniel\Bitfinex\Entities\Movement>}.
     */
    final public function depositHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $items = array_map(fn ($data) => new Movement($data), $content);
            $deposits = array_values(array_filter($items, fn (Movement $m) => $m->amount > 0));

            return ['deposits' => $deposits];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{withdrawals: list<\EwertonDaniel\Bitfinex\Entities\Movement>}.
     */
    final public function withdrawalHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $items = array_map(fn ($data) => new Movement($data), $content);
            $withdrawals = array_values(array_filter($items, fn (Movement $m) => $m->amount < 0));

            return ['withdrawals' => $withdrawals];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{alert: \EwertonDaniel\Bitfinex\Entities\Alert}.
     */
    final public function alertSet(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['alert' => new Alert($content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{deleted: bool|int}.
     */
    final public function alertDelete(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['deleted' => $content[0]]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{alerts: list<\EwertonDaniel\Bitfinex\Entities\Alert>}.
     */
    final public function alertList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['alerts' => array_map(fn ($data) => new Alert($data), $content)]);
    }

    // Positions related mappings
    /**
     * @return AuthenticatedBitfinexResponse with content array{positions: list<\EwertonDaniel\Bitfinex\Entities\Position>}.
     */
    final public function positions(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['positions' => array_map(fn ($data) => new Position($data), $content)]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{positions: list<\EwertonDaniel\Bitfinex\Entities\Position>}.
     */
    final public function positionsHistory(): AuthenticatedBitfinexResponse
    {
        return $this->positions();
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{positions: list<\EwertonDaniel\Bitfinex\Entities\Position>}.
     */
    final public function positionsSnapshot(): AuthenticatedBitfinexResponse
    {
        return $this->positions();
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{audit: mixed}.
     */
    final public function positionsAudit(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['audit' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{margin: mixed}.
     */
    final public function marginInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['margin' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{positions: list<\EwertonDaniel\Bitfinex\Entities\Position>}.
     */
    /**
     * Transforms the notification returned by the position claim endpoint, whose
     * DATA field holds the claimed position as a single, flat position array —
     * not a list. The response used to be read as a plain list of positions, so
     * a successful claim mapped each envelope field as a "position" and a
     * refused claim passed as a success.
     *
     * The reference's field table labels TYPE as `on-req`, but its own embedded
     * example shows `pm-req`; the table is boilerplate from the order submit page.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, position: \EwertonDaniel\Bitfinex\Entities\Position|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function positionsClaim(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'position' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data),
                    callback: fn () => new Position($notification->data)
                ),
            ];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{positions: list<\EwertonDaniel\Bitfinex\Entities\Position>}.
     */
    final public function positionsIncrease(): AuthenticatedBitfinexResponse
    {
        return $this->positions();
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{info: mixed}.
     */
    final public function positionIncreaseInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['info' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{result: mixed}.
     */
    final public function derivativePositionCollateral(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['result' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{limits: mixed}.
     */
    final public function derivativePositionCollateralLimits(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['limits' => $content]);
    }

    /**
     * Transforms the notification returned by the wallet transfer endpoint.
     *
     * The status used to be exposed and then ignored, so a refused transfer was
     * handed to the caller as a successful response carrying `status: 'ERROR'`.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, transferred: mixed, status: string|null, text: string|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function transferBetweenWallets(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'transferred' => $notification->data,
                'status' => $notification->status,
                'text' => $notification->text,
            ];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{invoice: mixed}.
     */
    final public function generateInvoice(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoice' => $content]);
    }

    /**
     * Transforms the notification returned by the withdraw endpoint, whose DATA
     * field holds the withdrawal record.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, withdrawal: \EwertonDaniel\Bitfinex\Entities\Withdrawal|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function withdrawal(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'withdrawal' => GetThis::ifTrueOrFallback(
                    boolean: is_array($notification->data),
                    callback: fn () => new Withdrawal($notification->data)
                ),
            ];
        });
    }

    /**
     * Transforms the notification returned by the settings write endpoint, whose
     * DATA field holds `[NUMBER_OF_SETTINGS]` — how many settings were created
     * or changed. The reference's field table types it as a string, but its own
     * embedded example shows an integer; the example prevails.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification, count: int|null}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function userSettingsWrite(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $notification = $this->succeededNotification($content);

            return [
                'notification' => $notification,
                'count' => GetThis::ifTrueOrFallback(
                    boolean: is_numeric($notification->data[0] ?? null),
                    callback: fn () => (int) $notification->data[0]
                ),
            ];
        });
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */
    final public function userSettingsRead(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }

    /**
     * Transforms the notification returned by the settings delete endpoint. The
     * single element its DATA carries is labelled only PLACEHOLDER in the
     * reference, so no meaning is asserted for it here: it stays raw on the
     * notification for whoever needs it.
     *
     * @return AuthenticatedBitfinexResponse with content array{notification: \EwertonDaniel\Bitfinex\Entities\Notification}.
     *
     * @throws BitfinexNotificationException When the API reports a status other than SUCCESS.
     */
    final public function userSettingsDelete(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => [
            'notification' => $this->succeededNotification($content),
        ]);
    }

    // Merchants (Bitfinex Pay) mappings
    /**
     * @return AuthenticatedBitfinexResponse with content array{invoice: mixed}.
     */
    final public function merchantInvoiceCreated(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoice' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{invoice: mixed}.
     */
    final public function merchantPostInvoiceCreated(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoice' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{invoices: mixed}.
     */
    final public function merchantInvoiceList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoices' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{invoices: mixed}.
     */
    final public function merchantInvoiceListPaginated(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoices' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{stats: mixed}.
     */
    final public function merchantInvoiceCountStats(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['stats' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{stats: mixed}.
     */
    final public function merchantInvoiceEarningsStats(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['stats' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{completed: mixed}.
     */
    final public function merchantInvoiceCompleted(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['completed' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{expired: mixed}.
     */
    final public function merchantInvoiceExpired(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['expired' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{conversions: mixed}.
     */
    final public function merchantCurrencyConversionList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['conversions' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{conversion: mixed}.
     */
    final public function merchantCurrencyConversionCreated(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['conversion' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{removed: mixed}.
     */
    final public function merchantCurrencyConversionRemoved(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['removed' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{limit: mixed}.
     */
    final public function merchantLimit(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['limit' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */
    final public function merchantSettingsWrite(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */
    final public function merchantSettingsWriteBatch(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */
    final public function merchantSettingsRead(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */
    final public function merchantSettingsList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{deposits: mixed}.
     */
    final public function merchantDepositsList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['deposits' => $content]);
    }

    /**
     * @return AuthenticatedBitfinexResponse with content array{deposits: mixed}.
     */
    final public function merchantUnlinkedDepositsList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['deposits' => $content]);
    }

    /**
     * Reads the notification envelope out of a raw response body and applies the
     * status gate — the one shared step of every notification transformer. How
     * DATA is interpreted stays with each endpoint's own method.
     *
     * @throws BitfinexNotificationException
     */
    private function succeededNotification(mixed $content): Notification
    {
        return $this->assertNotificationSucceeded(new Notification((array) $content));
    }

    /**
     * Lets a notification through when the API reported SUCCESS, and raises it
     * otherwise.
     *
     * The API signals a refused write in the body while answering HTTP 200, and
     * STATUS is documented as an open set, so anything other than SUCCESS is a
     * failure. DATA is not discarded: it reaches the caller through the exception,
     * because on a refused update or cancel it holds the order as it currently
     * stands on the exchange.
     *
     * @throws BitfinexNotificationException
     */
    private function assertNotificationSucceeded(Notification $notification): Notification
    {
        if ($notification->isSuccess()) {
            return $notification;
        }

        throw new BitfinexNotificationException($notification, $this->statusCode);
    }
}
