<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;

// The `Auth` dataset used to hand every authenticated test a pair of placeholder
// credentials, which meant those tests ran against the real API, failed on
// `apikey: digest invalid` and asserted nothing. They now go through
// `Tests\Support\BitfinexMock`, which queues the responses instead.

dataset('Bitfinex', [
    'Bitfinex' => function () {
        return new Bitfinex;
    },
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
