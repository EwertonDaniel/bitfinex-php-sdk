<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Core\Entities\LoginInfo;
use EwertonDaniel\Bitfinex\Core\Entities\Order;
use EwertonDaniel\Bitfinex\Core\Entities\Summary;
use EwertonDaniel\Bitfinex\Core\Entities\User;
use EwertonDaniel\Bitfinex\Core\Entities\Wallet;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

class AuthenticatedBitfinexResponse extends BitfinexResponse
{
    final public function generateToken(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['token' => GetThis::ifTrueOrFallback(isset($content[0]), fn () => $content[0])]);
    }

    final public function userInfo(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['user' => new User($content)]);
    }

    final public function summary(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['summary' => new Summary($content)]);
    }

    final public function loginHistory(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['loginInfo' => array_map(fn ($data) => new LoginInfo($data), $content)]);
    }

    final public function wallets(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['wallets' => array_map(fn ($data) => new Wallet($data), $content)]);
    }

    final public function orders(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => ['orders' => array_map(fn ($data) => new Order($data), $content)]);
    }
}
