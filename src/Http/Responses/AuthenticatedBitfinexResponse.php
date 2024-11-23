<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\ChangeLogItem;
use EwertonDaniel\Bitfinex\Entities\KeyPermission;
use EwertonDaniel\Bitfinex\Entities\LoginInfo;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Order;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Entities\Wallet;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class AuthenticatedBitfinexResponse
 *
 * Handles transformations of responses for authenticated endpoints.
 * Converts raw API response content into structured entities for easier handling.
 *
 * @author  Ewerton
 *
 * @contact contact@ewertondaniel.work
 */
class AuthenticatedBitfinexResponse extends BitfinexResponse
{
    /**
     * Transforms the response content for a generated token.
     *
     * @return BitfinexResponse Contains the token as an associative array ['token' => string].
     */
    final public function generateToken(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['token' => GetThis::ifTrueOrFallback(isset($content[0]), fn () => $content[0])]);
    }

    /**
     * Transforms the response content into a User entity.
     *
     * @return BitfinexResponse Contains the user information as a structured User entity.
     */
    final public function userInfo(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['user' => new User($content)]);
    }

    /**
     * Transforms the response content into a Summary entity.
     *
     * @return BitfinexResponse Contains the summary data as a structured Summary entity.
     */
    final public function summary(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['summary' => new Summary($content)]);
    }

    /**
     * Transforms the response content into an array of LoginInfo entities.
     *
     * @return BitfinexResponse Contains login history as an array of LoginInfo entities.
     */
    final public function loginHistory(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['history' => array_map(fn ($data) => new LoginInfo($data), $content)]);
    }

    /**
     * Transforms the response content into an array of KeyPermission entities.
     *
     * @return BitfinexResponse Contains key permissions as an array of KeyPermission entities.
     */
    final public function keyPermissions(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['permissions' => array_map(fn ($data) => new KeyPermission($data), $content)]);
    }

    final public function changelog(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['changelog' => array_map(fn ($data) => new ChangeLogItem($data), $content)]);
    }

    /**
     * Transforms the response content into an array of Wallet entities.
     *
     * @return BitfinexResponse Contains wallets as an array of Wallet entities.
     */
    final public function wallets(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['wallets' => array_map(fn ($data) => new Wallet($data), $content)]);
    }

    /**
     * Transforms the response content into an array of Order entities.
     *
     * @return BitfinexResponse Contains orders as an array of Order entities.
     */
    final public function retrieveOrders(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }

    /**
     * Transforms the response content into a single Order entity.
     *
     * @return BitfinexResponse Contains a submitted order as a structured Order entity.
     */
    final public function submitOrder(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['order' => new Order($content)]);
    }

    final public function movements(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movements' => array_map(fn ($data) => new Movement($data), $content)]);
    }

    final public function movementInfo(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['movement' => new Movement($content)]);
    }
}
