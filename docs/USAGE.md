# Bitfinex PHP SDK - Public Endpoints Usage Guide

This guide describes how to use the public endpoints provided by the `bitfinex-php-sdk`.

## Installation

To install the package using Composer, run the following command:

```bash
composer require ewertondaniel/bitfinex-php-sdk
```

## Requirements

- PHP 8.1 or higher.
- Guzzle HTTP client library.
- Laravel 9, 10, or 11 (optional for Laravel-based features).

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

### Ticker for a Single Pair

Retrieve the ticker for a specific pair to see the current state of the market for that pair:

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$symbol = 'BTCUSD'; // Specify the trading pair e.g. 'BTCUSD', 'XMRUSD','ETHUSD', etc or funding currency symbol e.g. 'BTC', 'ETH', etc.
$type = 'trading'; // Choose 'trading' or 'funding'

$response = Bitfinex::public()->ticker($symbol, $type);

$response->content; // Displays the ticker for the pair or currency
```

### Tickers

Retrieve multiple tickers with a single request. The tickers include data such as the best bid, ask prices, last trade price, and daily volume.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$symbols = ['BTCUSD', 'ETHUSD']; // Add multiple pairs or currency if funding as needed
$type = 'trading'; // Choose either 'trading' or 'funding'

$tickers = Bitfinex::public()->tickers($symbols, $type);

$tickers->content; // Displays the tickers for the specified pairs or currencies group by symbol e.g. 'tBTCUSD', 'tETHUSD', etc. or funding currency symbol e.g. 'fBTC', 'fETH', etc.
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

### Trades

Retrieve past trades for a specific symbol, including details such as price, size, and time.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$symbol = 'BTCUSD'; // Specify the trading pair
$type = 'trading'; // Choose 'trading' or 'funding'
$limit = 125; // Optional: Limit the number of results
$sort = -1; // Optional: Sorting order (-1 for descending, 1 for ascending)
$start = int; // min -9223372036854776000 max 9223372036854776000 | If start is given, only records with MTS >= start (milliseconds) will be given as response.
$end = int; // min -9223372036854776000 max 9223372036854776000 |  If end is given, only records with MTS <= end (milliseconds) will be given as response.

$response = Bitfinex::public()->trades($symbol, $type, $limit, $sort, $start, $end);

$response->content; // Displays the past trades for the specified pair
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
- `map:*` responses viram mapas associativos (ex.: alias => símbolo).
- `list:*` respostas viram arrays de string (pares, moedas etc.).
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
- Conf responses are handled by `Http\Responses\Configs\ConfigsTransformer`, que aplica estratégias por modo (`map`, `list`, `info:*`).

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
