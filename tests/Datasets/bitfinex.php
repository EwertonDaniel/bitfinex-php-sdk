<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

dataset('Bitfinex', [
    'Bitfinex' => function () {
        return new Bitfinex;
    },
]);

dataset('Auth', [
    'Credentials' => function () {
        return new BitfinexCredentials(
            apiKey: 'you api key',
            apiSecret: 'your api secret'
        );
    },
]);

dataset('Movement Id', [
    'id' => '123456789',
]);

dataset('Pair/Currency and Type', [
    'Trading' => ['XMRUST', BitfinexType::TRADING],
    'Funding' => ['XMR', BitfinexType::FUNDING],
]);

dataset('Pairs/Currencies and Type', [
    'Trading' => [['XMRUST', 'EURUSD'], BitfinexType::TRADING],
    'Funding' => [['UST', 'USD'], BitfinexType::FUNDING],
]);

dataset('Currencies', [
    'Euro/Dollar' => ['EUR', 'USD'],
    'Bitcoin/Dollar' => ['BTC', 'USD'],
    'Monero/Euro' => ['XMR', 'EUR'],
    'Ethereum/Dollar' => ['ETH', 'USD'],
]);

dataset('Pairs', [
    'Euro/Dollar, Bitcoin/Dollar' => [['EURUSD', 'BTCUSD']],
    'Monero/Dollar, Ethereum/Dollar' => [['XMRUSD', 'ETHUSD']],
]);
