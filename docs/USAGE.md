# Bitfinex PHP SDK - Public Endpoints Usage Guide

This guide describes how to use the public endpoints provided by the `bitfinex-php-sdk`.

## Installation

To install the package using Composer, run the following command:

```bash
composer require ewertondaniel/bitfinex-php-sdk
```


## Index

- Public
  - [Symbols and BitfinexType](#symbols-and-bitfinextype)
  - [Platform Status](#platform-status)
  - [Ticker (single)](#ticker-single)
  - [Tickers (multiple markets)](#tickers-multiple-markets)
  - [Ticker History](#ticker-history)
  - [Trades](#trades)
  - [Book](#book)
  - [Stats](#stats)
  - [Candles](#candles-ohlcv)
  - [Configs (Conf)](#configs-conf)
  - [Derivatives Status & History](#derivatives-status--history)
  - [Liquidations](#liquidations)
  - [Leaderboards](#leaderboards-rankings)
  - [Funding Statistics](#funding-statistics)
  - [Market Average Price](#market-average-price-calc)
  - [Foreign Exchange Rate](#foreign-exchange-rate)
- Authenticated
  - [Initialize](#authenticated-api-endpoints)
  - [Wallets](#wallets)
  - [Orders](#orders)
  - [Positions](#positions)
  - [Funding](#funding)
  - [Account Actions](#account-actions)
  - [Merchants (Bitfinex Pay)](#merchants-bitfinex-pay)

## Requirements

- PHP 8.1 or higher.
- Guzzle HTTP client library.
- Laravel 10, 11, or 12 (optional for Laravel-based features).

## Symbols and BitfinexType

Bitfinex REST v2 distinguishes between trading pairs and funding currencies via prefixes:

- Trading symbols use a `t` prefix (e.g., `tBTCUSD`).
- Funding symbols use an `f` prefix (e.g., `fUSD`).

This SDK provides a helper enum `EwertonDaniel\Bitfinex\Enums\BitfinexType` to derive symbols:

```php
use EwertonDaniel\Bitfinex\Enums\BitfinexType;

// Trading pair → prefixed symbol
$trading = BitfinexType::TRADING;
$tSymbol = $trading->symbol('BTCUSD'); // "tBTCUSD"

// Funding currency → prefixed symbol
$funding = BitfinexType::FUNDING;
$fSymbol = $funding->symbol('USD'); // "fUSD"

// For multiple items (comma-separated list for /tickers)
$list = BitfinexType::TRADING->symbols(['BTCUSD','ETHUSD']); // "tBTCUSD,tETHUSD"
```

Most public services offer explicit methods `byPair('BTCUSD')` and `byCurrency('USD')` so you do not need to manually prefix symbols.

## Public API Endpoints

### Default response structure:

```json
{
    "success": "boolean",
    "statusCode": "int",
    "headers": "array",
    "content": "mixed"
}
```

### Platform Status

Retrieve the current operational status of the Bitfinex platform (either "Operative" or "Maintenance"):

```php

use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$response = Bitfinex::public()->platformStatus();
$response->content->status; // Displays the current platform status. 
```

### Ticker (single)

Retrieve the ticker for a specific pair to see the current state of the market for that pair:

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Trading
$resp = Bitfinex::public()->ticker()->byPair('BTCUSD');
$resp->content['ticker']; // EwertonDaniel\Bitfinex\Entities\TradingPair

// Funding
$resp = Bitfinex::public()->ticker()->byCurrency('USD');
$resp->content['ticker']; // EwertonDaniel\Bitfinex\Entities\FundingCurrency
```

### Tickers (multiple markets)

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Trading (multiple pairs)
$resp = Bitfinex::public()->ticker()->byPairs(['BTCUSD','ETHUSD']);
$resp->content['tickers']; // list<TradingPair>

// Funding (multiple currencies)
$resp = Bitfinex::public()->ticker()->byCurrencies(['USD','EUR']);
$resp->content['tickers']; // list<FundingCurrency>
```

### Ticker History

Retrieve the history of tickers for specific pairs. It provides historical data of the best bid and ask prices at hourly intervals, up to one year.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$symbols = ['tBTCUSD', 'tETHUSD']; // Specify the trading pairs
$limit = 100; // Optional: Limit the number of results
$start = int; // min -9223372036854776000 max 9223372036854776000 | If start is given, only records with MTS >= start (milliseconds) will be given as response.
$end = int; // min -9223372036854776000 max 9223372036854776000 |  If end is given, only records with MTS <= end (milliseconds) will be given as response.

$response = Bitfinex::public()->tickerHistory($symbols, $limit, $start, $end);

$response->content; // Displays the historical data for the specified pairs
```

### Book

```php
$book = Bitfinex::public()->book();

// Trading order book
$resp = $book->byPair('BTCUSD');
$resp->content['books']; // list<BookTrading>

// Funding order book
$resp = $book->byCurrency('USD');
$resp->content['books']; // list<BookFunding>
```

### Trades

Retrieve historical trades for a pair or funding currency:

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$trades = Bitfinex::public()->trades(limit: 125, sort: -1);

// Trading
$resp = $trades->byPair('BTCUSD');
$resp->content['trades']; // list<PairTrade>

// Funding
$resp = $trades->byCurrency('USD');
$resp->content['trades']; // list<CurrencyTrade>
```

### Stats

Retrieve platform statistics such as position size or funding size. Use `byPair` for trading pairs and `byCurrency` for funding currencies.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Example: long position size for BTCUSD, 1-minute intervals, historical series
$stats = Bitfinex::public()->stats(
    key: 'pos.size',    // e.g., 'pos.size', 'credits.size'
    size: '1m',         // interval: '1m', '5m', '1h', '1d', ...
    sidePair: 'long',   // side or pair depending on key
    section: 'hist'     // 'last' or 'hist'
);

$resp = $stats->byPair('BTCUSD', sort: -1, start: '2024-01-01', limit: 100);
$resp->content['stats']; // array<Stat>

// Funding currency example (e.g., credits size for USD)
$stats = Bitfinex::public()->stats('credits.size', '1h', 'tBTCUSD', 'hist');
$resp = $stats->byCurrency('USD', limit: 50);
$resp->content['stats']; // array<Stat>
```

### Foreign Exchange Rate

Calculate the exchange rate between two currencies:

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$response = Bitfinex::public()->foreignExchangeRate('USD', 'EUR');

$response->content; // Displays the exchange rate from USD to EUR
```

### Candles (OHLCV)

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// 1-minute candles for BTCUSD (hist)
$candles = Bitfinex::public()->candles('1m')->byPair('BTCUSD', start: '2024-01-01', limit: 100, sort: -1);
$candles->content['candles']; // array<Candle>
```

### Configs (Conf)

```php
// Multiple keys at once; returns array<ConfigEntry>
$conf = Bitfinex::public()->configs()->get([
    'map' => ['currency:sym'],
    'list' => ['pair:exchange'],
    'info' => ['pair', 'tx:status'],
]);
$conf->content['configs'];
```

Notes:
- Structured keys expand to: `pub:map:currency:sym`, `pub:list:pair:exchange`, `pub:info:pair`, `pub:info:tx:status`.
- `map:*` responses become associative maps (ex.: alias => símbolo).
- `list:*` responses become a list of strings (pares, moedas etc.).
- `info:pair*` mapeia para `array<PairInfo>`; `info:tx:status` para `array<TxStatus>`.


- Keys are documented by Bitfinex (examples: `pub:map:currency:sym`, `pub:list:pair:exchange`).
- Unknown keys are ignored (only returned when present).

- `pub:info:pair` / `pub:info:pair:futures` (Pair info)
  - Returns `array<PairInfo>` where each item is `[PAIR, block1, block2]`.
  - `PairInfoBlock` exposes: `minOrderSize` [3], `maxOrderSize` [4], `initialMargin` [8], `minMargin` [9].
  - Example:
    ```php
    $pairs = Bitfinex::public()->configs()->get('pub:info:pair');
    $items = $pairs->content['configs'][0]->value; // array<PairInfo>
    $items[0]->pair; $items[0]->one->minOrderSize;
    ```

- `pub:info:tx:status` (Deposit/withdraw status per method)
  - Returns `array<TxStatus>` where each row maps: `method`, `depositStatus` [1], `withdrawStatus` [2], `paymentIdDeposit` [7], `paymentIdWithdraw` [8], `depositConfirmationsRequired` [11].
  - Example:
    ```php
    $tx = Bitfinex::public()->configs()->get('pub:info:tx:status');
    $items = $tx->content['configs'][0]->value; // array<TxStatus>
    $items[0]->method; $items[0]->depositStatus;
    ```

### Derivatives Status & History

```php
// Current snapshot
$ds = Bitfinex::public()->derivativesStatus()->get(['tBTCF0:USD']);
$ds->content['items']; // array<DerivativeStatus>

// History window (use derivativesStatusHistory entry)
$dsHist = Bitfinex::public()->derivativesStatusHistory()->get('tBTCF0:USD', start: 1700000000000, end: 1700100000000, limit: 100, sort: -1);
$dsHist->content['items'];
```

Notes:
- `keys`: list of derivative symbols (e.g., `tBTCF0:USD`).
- History is controlled via `start`, `end`, `limit`, `sort`.



Architecture note:
- Public responses are transformed by dedicated classes in `Http\Responses\Public\Transformers` selected via a `TransformerFactory`.
- Conf responses are handled by `Http\Responses\Configs\ConfigsTransformer`, which applies strategies by mode (`map`, `list`, `info:*`).

### Liquidations

```php
$liq = Bitfinex::public()->liquidations()->get(start: 1700000000000, end: 1700100000000, limit: 100, sort: -1);
$liq->content['liquidations']; // array<Liquidation>
```

Notes:
- Field mapping in `Liquidation` (indexes per API):
  - [1] `posId` (int): Position ID
  - [2] `mts` (int): Millisecond epoch timestamp
  - [4] `symbol` (string): Trading pair (e.g., tBTCUSD)
  - [5] `amount` (float): Position size (>0 long, <0 short)
  - [6] `basePrice` (float): Entry price of position
  - [8] `isMatch` (int): 0 initial trigger, 1 market execution
  - [9] `isMarketSold` (int): 0 acquired by system, 1 sold on market
  - [11] `priceAcquired` (float): Price at which the position has been acquired

### Leaderboards (Rankings)

```php
// Example: key/timeframe depend on Bitfinex docs
$rank = Bitfinex::public()->leaderboards('pnl', '1D')->byPair('BTCUSD', limit: 50, sort: -1);
$rank->content['items']; // array<LeaderboardEntry>
```

Notes:
- `key`: ranking metric (consult Bitfinex docs; e.g., `pnl`, `vol`).
- `timeframe`: period (e.g., `1D`, `7D`).
- `section`: `hist` (history) or `last` (latest), default `hist`.

### Funding Statistics

```php
$stats = Bitfinex::public()->fundingStats()->byCurrency('USD', start: '2024-01-01', limit: 100, sort: -1);
$stats->content['items']; // array<FundingStat>
```

Notes:
- Currency is a funding code (e.g., `USD`, `BTC`), not prefixed.
- Filters: `start`, `end`, `limit`, `sort`.

### Market Average Price (Calc)

```php
// Payload shape per Bitfinex docs
$map = Bitfinex::public()->marketAveragePrice(['symbol' => 'tBTCUSD', 'amount' => '0.5']);
$map->content['result']; // MarketAveragePriceResult
```

## Authenticated Endpoints

Below are common authenticated flows relevant to deposits/withdrawals. Set credentials via env (`BITFINEX_API_KEY`, `BITFINEX_API_SECRET`) or pass them explicitly.

### Credentials (Laravel Facade)

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Uses credentials from config/env
$auth = Bitfinex::authenticated();
```

### Credentials (Vanilla PHP)

```php
use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

$bf = new Bitfinex();
$auth = $bf->authenticated(new BitfinexCredentials('API_KEY', 'API_SECRET'));
```

### Deposit Address and List

```php
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;

// Single deposit address
$addr = $auth->accountAction()->depositAddress(BitfinexWalletType::EXCHANGE, 'crypto');
$addr->content['address'];

// Paginated list of addresses (e.g., method: 'crypto')
$list = $auth->accountAction()->depositAddressList('crypto', page: 1, pageSize: 20);
$list->content['addresses']['items'];
```

### Movements (Deposits and Withdrawals)

You can fetch movements by currency with optional filters `start`, `end` (timestamps or Carbon strings) and `limit`.

```php
// All movements for BTC (mixed deposits/withdrawals)
$all = $auth->accountAction()->movements('BTC', start: '2024-01-01', end: '2024-12-31', limit: 200);
$all->content['movements']; // array<Movement>

// Only deposits (amount > 0)
$deposits = $auth->accountAction()->depositHistory('BTC', limit: 100);
$deposits->content['deposits']; // array<Movement>

// Only withdrawals (amount < 0)
$withdrawals = $auth->accountAction()->withdrawalHistory('BTC', limit: 100);
$withdrawals->content['withdrawals']; // array<Movement>
```

### Movement Details

```php
$info = $auth->accountAction()->movementInfo(1234567890);
$info->content['movement']; // Movement
```

### Positions (Authenticated)

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$auth = Bitfinex::authenticated();

// Margin info (e.g., 'base' or specific key)
$margin = $auth->positions()->marginInfo('base');
$margin->content['margin'];

// Open positions
$open = $auth->positions()->retrieve();
$open->content['positions']; // array<Position>

// Claim or increase position
$claim = $auth->positions()->claim('BTCUSD', 0.1);
$inc   = $auth->positions()->increase('BTCUSD', 0.05, price: 35000);

// Increase info (what-if)
$info = $auth->positions()->increaseInfo('BTCUSD', 0.05);
$info->content['info'];

// History / Snapshot / Audit
$hist = $auth->positions()->history(start: 1700000000000, end: 1700100000000, limit: 50, sort: -1);
$snap = $auth->positions()->snapshot();
$audit = $auth->positions()->audit();

// Derivative collateral
$setColl = $auth->positions()->setDerivativeCollateral('BTCUSD', 100.0);
$limits = $auth->positions()->derivativeCollateralLimits('BTCUSD');
```


### Funding (Authenticated)

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$auth = Bitfinex::authenticated();

// Active offers
$offers = $auth->funding()->activeOffers('USD');
$offers->content['offers']; // array<FundingOffer>

// Submit / cancel offers
$submit = $auth->funding()->submitOffer('USD', amount: 100.0, rate: 0.0002, period: 2);
$cancel = $auth->funding()->cancelOffer(id: 123456);
$cancelAll = $auth->funding()->cancelAllOffers('USD');

// Close loan/credit, auto-renew, keep
$close = $auth->funding()->close(id: 123456);
$auto = $auth->funding()->autoRenew(id: 123456, enabled: true);
$keep = $auth->funding()->keep(id: 123456);

// Loans / Credits / Trades
$loans = $auth->funding()->loans('USD');
$credits = $auth->funding()->credits('USD');
$trades = $auth->funding()->trades('USD', limit: 50, sort: -1);

// History and info
$offersHist = $auth->funding()->offersHistory('USD', limit: 50);
$loansHist = $auth->funding()->loansHistory('USD', limit: 50);
$creditsHist = $auth->funding()->creditsHistory('USD', limit: 50);
$info = $auth->funding()->info('funding.size');
```


## Authenticated API Endpoints

Below are concise examples for common authenticated flows. In Laravel, set `BITFINEX_API_KEY` and `BITFINEX_API_SECRET` in `.env`.

### Initialize (Laravel Facade)
```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Uses credentials from config/env
$auth = Bitfinex::authenticated()->generateToken();
```

### Initialize (Vanilla PHP)
```php
use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

$bf = new Bitfinex();
$auth = $bf->authenticated(new BitfinexCredentials('API_KEY', 'API_SECRET'))->generateToken();
```

### Wallets
```php
$resp = $auth->wallets()->get();
$resp->content['wallets']; // list<Wallet>
```

### Orders

### Orders (retrieve)
```php
use EwertonDaniel\Bitfinex\Enums\BitfinexType;

// All symbols
$resp = $auth->orders()->retrieve();
$resp->content['orders']; // list<Order>

// By trading pair
$resp = $auth->orders()->retrieve('XMRUSD');
$resp->content['orders'];
```

### Orders (history & trades)
```php
// Orders history
$resp = $auth->orders()->history(limit: 50);
$resp->content['orders'];

// Trades for a given symbol (history)
$resp = $auth->orders()->tradesHistory(BitfinexType::TRADING, 'XMRUSD', limit: 50, sort: -1);
$resp->content['trades'];
```

### Positions
```php
// Open positions
$resp = $auth->positions()->retrieve();
$resp->content['positions']; // list<Position>

// Margin info
$resp = $auth->positions()->marginInfo('base');
$resp->content['margin'];
```

### Funding
```php
// Active funding offers for USD
$resp = $auth->funding()->activeOffers('USD');
$resp->content['offers'];
```

### Account Actions
```php
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;

// User info
$resp = $auth->accountAction()->userInfo();
$resp->content['user'];

// Deposit address
$resp = $auth->accountAction()->depositAddress(BitfinexWalletType::EXCHANGE, 'monero');
$resp->content['address'];

// Movements
$resp = $auth->accountAction()->movements('USD', limit: 50);
$resp->content['movements'];

// Alerts
$resp = $auth->accountAction()->alertSet('XMRUSD', 250);   // create
$resp = $auth->accountAction()->alertDelete('XMRUSD', 250); // delete
```

### Merchants (Bitfinex Pay)
```php
$resp = $auth->merchants()->submitInvoice([
  'wallet'   => 'exchange',
  'currency' => 'USD',
  'amount'   => '10.00',
  'label'    => 'Order #123',
]);
$resp->content['invoice'];
```


### Orders (submit/update/cancel)
```php
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;

// Submit
$resp = $auth->orders()->submit(
  type: OrderType::EXCHANGE_LIMIT,
  action: BitfinexAction::BUY,
  pair: 'XMRUSD',
  amount: 0.01,
  price: 140
);
$resp->content['order'];

// Update
$resp = $auth->orders()->update(id: 123456, price: 145);

// Cancel
$resp = $auth->orders()->cancel(id: 123456);

// Multi operations
$ops = [
  ['type' => 'LIMIT', 'symbol' => 'tXMRUSD', 'price' => '140', 'amount' => '0.01'],
  ['id' => 123456, 'type' => 'CANCEL'],
];
$resp = $auth->orders()->multi($ops);

// Cancel multiple
$resp = $auth->orders()->cancelMultiple([111, 222, 333]);
```


### Positions (claim/increase)
```php
// Claim
$resp = $auth->positions()->claim('XMRUSD', amount: 0.01);

// Increase
$resp = $auth->positions()->increase('XMRUSD', amount: 0.01, price: 150);

// Increase info (what-if)
$resp = $auth->positions()->increaseInfo('XMRUSD', amount: 0.01);
```


### Funding (offers/loans/credits/trades)
```php
// Submit offer
$resp = $auth->funding()->submitOffer('USD', amount: 100.0, rate: 0.0002, period: 2);

// Cancel / Cancel all
$resp = $auth->funding()->cancelOffer(id: 123456);
$resp = $auth->funding()->cancelAllOffers('USD');

// Close loan/credit, auto-renew, keep
$resp = $auth->funding()->close(id: 123456);
$resp = $auth->funding()->autoRenew(id: 123456, enabled: true);
$resp = $auth->funding()->keep(id: 123456);

// History
$resp = $auth->funding()->offersHistory('USD', limit: 50);
$resp = $auth->funding()->loansHistory('USD', limit: 50);
$resp = $auth->funding()->creditsHistory('USD', limit: 50);
$resp = $auth->funding()->trades('USD', limit: 50);

// Info
$resp = $auth->funding()->info('funding.size');
```


### Account Actions (settings, alerts, movements)
```php
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;

// Settings
$resp = $auth->accountAction()->userSettingsWrite(['key' => 'value']);
$resp = $auth->accountAction()->userSettingsRead();
$resp = $auth->accountAction()->userSettingsDelete(['key']);

// Alerts
$resp = $auth->accountAction()->alertSet('XMRUSD', 250);
$resp = $auth->accountAction()->alertList('price');
$resp = $auth->accountAction()->alertDelete('XMRUSD', 250);

// Movements
$resp = $auth->accountAction()->movements('USD', limit: 50);
$resp = $auth->accountAction()->movementInfo(123456);

// Transfer between wallets
$resp = $auth->accountAction()->transferBetweenWallets(); // Provide body via request builder before execution if needed
```


### Merchants (Bitfinex Pay) (invoices/settings/conversions)
```php
// Invoices
$resp = $auth->merchants()->submitInvoice(['wallet' => 'exchange', 'currency' => 'USD', 'amount' => '10.00']);
$resp = $auth->merchants()->submitPostInvoice(['wallet' => 'exchange', 'currency' => 'USD', 'amount' => '10.00']);
$resp = $auth->merchants()->invoiceList(['status' => 'pending']);
$resp = $auth->merchants()->invoiceListPaginated(page=1, pageSize=30);
$resp = $auth->merchants()->completeInvoice(['invoiceId' => '...']);
$resp = $auth->merchants()->expireInvoice(['invoiceId' => '...']);

// Conversions
$resp = $auth->merchants()->currencyConversionList();
$resp = $auth->merchants()->addCurrencyConversion(['from' => 'USD', 'to' => 'EUR']);
$resp = $auth->merchants()->removeCurrencyConversion(['from' => 'USD', 'to' => 'EUR']);

// Settings & limits
$resp = $auth->merchants()->merchantSettingsWrite(['key' => 'value']);
$resp = $auth->merchants()->merchantSettingsWriteBatch([['key' => 'value']]);
$resp = $auth->merchants()->merchantSettingsRead(['keys' => ['key']]);
$resp = $auth->merchants()->merchantSettingsList();
$resp = $auth->merchants()->merchantLimit();

// Deposits
$resp = $auth->merchants()->depositsList(['currency' => 'USD']);
$resp = $auth->merchants()->unlinkedDepositsList(['currency' => 'USD']);
```
