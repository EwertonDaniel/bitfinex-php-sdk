<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\Alert;
use EwertonDaniel\Bitfinex\Entities\ChangeLogItem;
use EwertonDaniel\Bitfinex\Entities\DepositAddress;
use EwertonDaniel\Bitfinex\Entities\KeyPermission;
use EwertonDaniel\Bitfinex\Entities\LedgerEntry;
use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Entities\FundingOffer;
use EwertonDaniel\Bitfinex\Entities\FundingLoan;
use EwertonDaniel\Bitfinex\Entities\FundingCredit;
use EwertonDaniel\Bitfinex\Entities\FundingTrade;
use EwertonDaniel\Bitfinex\Entities\LoginInfo;
use EwertonDaniel\Bitfinex\Entities\Position;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Order;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Entities\Wallet;
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
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
                        BitfinexWalletType::tryFrom($data[1]),
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
     * @return AuthenticatedBitfinexResponse with content array{order: \EwertonDaniel\Bitfinex\Entities\Order|list<\EwertonDaniel\Bitfinex\Entities\Order>}.
     */


    final public function submitOrder(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'order' => GetThis::ifTrueOrFallback(
                    boolean: is_array($content[4]),
                    callback: fn () => array_map(fn ($data) => new Order($data), $content[4]),
                    fallback: fn () => new Order($content)
                ),
            ]
        );
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{orders: list<\EwertonDaniel\Bitfinex\Entities\Order>}.
     */


    final public function ordersHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{symbol: string, trades: list<\EwertonDaniel\Bitfinex\Entities\PairTrade|\EwertonDaniel\Bitfinex\Entities\CurrencyTrade>}.
     */


    final public function orderTrades(string $symbol, \EwertonDaniel\Bitfinex\Enums\BitfinexType $type): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'trades' => array_map(
                    fn ($trade) => match ($type) {
                        \EwertonDaniel\Bitfinex\Enums\BitfinexType::TRADING => new PairTrade($symbol, $trade),
                        \EwertonDaniel\Bitfinex\Enums\BitfinexType::FUNDING => new CurrencyTrade($symbol, $trade),
                    },
                    $content
                ),
            ]
        );
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{symbol: string, trades: list<\EwertonDaniel\Bitfinex\Entities\PairTrade|\EwertonDaniel\Bitfinex\Entities\CurrencyTrade>}.
     */


    final public function tradesHistory(string $symbol, \EwertonDaniel\Bitfinex\Enums\BitfinexType $type): AuthenticatedBitfinexResponse
    {
        return $this->orderTrades($symbol, $type);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{ledgers: list<\EwertonDaniel\Bitfinex\Entities\LedgerEntry>}.
     */


    final public function ledgers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['ledgers' => array_map(fn ($row) => new LedgerEntry($row), $content)]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{results: mixed}.
     */


    final public function orderMultiOp(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['results' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{results: mixed}.
     */


    final public function orderCancelMulti(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['results' => $content]);
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
     * @return AuthenticatedBitfinexResponse with content array{offer: \EwertonDaniel\Bitfinex\Entities\FundingOffer}.
     */


    final public function fundingOfferSubmitted(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['offer' => new FundingOffer($content)]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{cancelled: mixed}.
     */


    final public function cancelFundingOffer(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['cancelled' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{results: mixed}.
     */


    final public function cancelAllFundingOffers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['results' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{closed: mixed}.
     */


    final public function fundingClose(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['closed' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{autorenew: mixed}.
     */


    final public function fundingAutoRenew(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['autorenew' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{kept: mixed}.
     */


    final public function keepFunding(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['kept' => $content]);
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


    final public function positionsClaim(): AuthenticatedBitfinexResponse
    {
        return $this->positions();
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
     * @return AuthenticatedBitfinexResponse with content array{transferred: mixed}.
     */


    final public function transferBetweenWallets(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['transferred' => $content[0] ?? $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{invoice: mixed}.
     */


    final public function generateInvoice(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['invoice' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{withdrawal: mixed}.
     */


    final public function withdrawal(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['withdrawal' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */


    final public function userSettingsWrite(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{settings: mixed}.
     */


    final public function userSettingsRead(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['settings' => $content]);
    }
    /**
     * @return AuthenticatedBitfinexResponse with content array{deleted: mixed}.
     */


    final public function userSettingsDelete(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['deleted' => $content]);
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
}
