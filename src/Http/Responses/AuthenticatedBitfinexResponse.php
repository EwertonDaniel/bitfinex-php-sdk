<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\Alert;
use EwertonDaniel\Bitfinex\Entities\ChangeLogItem;
use EwertonDaniel\Bitfinex\Entities\DepositAddress;
use EwertonDaniel\Bitfinex\Entities\KeyPermission;
use EwertonDaniel\Bitfinex\Entities\LoginInfo;
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
 * @contact contact@ewertondaniel.work
 */
class AuthenticatedBitfinexResponse extends BitfinexResponse
{
    final public function generateToken(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['token' => GetThis::ifTrueOrFallback(isset($content[0]), fn () => $content[0])]);
    }

    final public function userInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['user' => new User($content)]);
    }

    final public function summary(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['summary' => new Summary($content)]);
    }

    final public function loginHistory(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['history' => array_map(fn ($data) => new LoginInfo($data), $content)]);
    }

    final public function keyPermissions(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['permissions' => array_map(fn ($data) => new KeyPermission($data), $content)]);
    }

    final public function changelog(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['changelog' => array_map(fn ($data) => new ChangeLogItem($data), $content)]);
    }

    final public function depositAddress(BitfinexWalletType $walletType): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['address' => new DepositAddress($walletType, $content)]);
    }

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

    final public function wallets(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['wallets' => array_map(fn ($data) => new Wallet($data), $content)]);
    }

    final public function retrieveOrders(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }

    final public function balanceAvailableForOrdersOffers(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['available' => $content[0]]);
    }

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

    final public function movements(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movements' => array_map(fn ($data) => new Movement($data), $content)]);
    }

    final public function movementInfo(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movement' => new Movement($content)]);
    }

    final public function alertSet(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['alert' => new Alert($content)]);
    }

    final public function alertDelete(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['deleted' => $content[0]]);
    }

    final public function alertList(): AuthenticatedBitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['alerts' => array_map(fn ($data) => new Alert($data), $content)]);
    }
}
