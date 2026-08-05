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
  - [Initialize](#initialize)
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

The history call lives on `ticker()`, not directly on `public()`: `ticker()->history(...)`, not `tickerHistory(...)`.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Pairs without the 't' prefix; it is added automatically.
$response = Bitfinex::public()->ticker()->history(['BTCUSD', 'ETHUSD'], limit: 100);

$response->content; // array<string, list<TickerHistory>> keyed by pair, e.g. 'BTCUSD' => [...]
```

`start` and `end` (milliseconds, or a Carbon-parsable string) restrict the window; the endpoint only keeps up to one year of history, so a window older than that returns an empty array for the affected pairs:

```php
$response = Bitfinex::public()->ticker()->history(
    pairs: ['BTCUSD', 'ETHUSD'],
    limit: 100,
    start: '2026-07-01',
    end: '2026-08-01'
);
```

### Book

`book()` defaults to precision `P0` (aggregated price levels). Pass `BookPrecision::R0` for the raw, per-order book; its rows carry an order/offer id instead of an aggregate count and map to a different pair of entities.

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$book = Bitfinex::public()->book();

// Trading order book (aggregated, P0)
$resp = $book->byPair('BTCUSD');
$resp->content['books']; // list<BookTrading>

// Funding order book (aggregated, P0)
$resp = $book->byCurrency('USD');
$resp->content['books']; // list<BookFunding>
```

```php
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$rawBook = Bitfinex::public()->book(BookPrecision::R0);

// Trading order book (raw, one row per order)
$resp = $rawBook->byPair('BTCUSD');
$resp->content['books']; // list<BookTradingRaw>

// Funding order book (raw, one row per offer)
$resp = $rawBook->byCurrency('USD');
$resp->content['books']; // list<BookFundingRaw>
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
    key: 'pos.size',    // e.g., 'pos.size', 'funding.size', 'credits.size', 'vwap'
    size: '1m',         // interval: '1m', '5m', '1h', '1d', ...
    sidePair: 'long',   // fourth path segment — see below
    section: 'hist'     // 'last' or 'hist'
);

$resp = $stats->byPair('BTCUSD', sort: -1, start: '2024-01-01', limit: 100);
$resp->content['stats']; // array<Stat>

// Most keys take no fourth segment: omit sidePair entirely.
Bitfinex::public()->stats('funding.size', '1m')->byCurrency('USD', limit: 50);
Bitfinex::public()->stats('credits.size', '1m')->byCurrency('USD', limit: 50);
Bitfinex::public()->stats('vwap', '1d')->byPair('BTCUSD', limit: 50);
Bitfinex::public()->stats('vol.1d', '30m')->byPair('BFX', limit: 50);

// Only these two take one:
Bitfinex::public()->stats('pos.size', '1m', 'long')->byPair('BTCUSD', limit: 50);
Bitfinex::public()->stats('credits.size.sym', '1m', 'tBTCUSD')->byCurrency('USD', limit: 50);
```

`sidePair` is the fourth path segment, and only `pos.size` (`long`/`short`) and
`credits.size.sym` (a trading pair) accept one. Supplying it for any other key
builds `funding.size:1m:fUSD:` — note the trailing colon — which the API answers
with a literal `null` under HTTP 200. That is why it defaults to null.

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

// Funding candles need a period on the symbol. The default aggregates the
// 2-to-30-day range, which is what the funding chart on the website shows.
$candles = Bitfinex::public()->candles('1m')->byCurrency('USD', limit: 100);

// A single period instead of an aggregate:
$candles = Bitfinex::public()->candles('1m')->byCurrency('USD', period: 'p30', limit: 100);
```

Without a period, `trade:1m:fUSD` answers HTTP 200 with a literal `null`, so
`byCurrency()` returned nothing at all before the period was added.

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

`get()` is a snapshot only: it does not accept `start`, `end`, `limit` or `sort`, and defaults `keys` to `ALL` when omitted. Historical data comes from `history($symbol, ...)`, which requires a single symbol because the endpoint carries it in the path and returns a different row layout (`DerivativeStatusHistory`, no leading `KEY` field).

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Current snapshot for one or more symbols
$ds = Bitfinex::public()->derivativesStatus()->get(['tBTCF0:USTF0']);
$ds->content['items']; // array<DerivativeStatus>

// History window for a single symbol
$dsHist = Bitfinex::public()->derivativesStatus()->history(
    symbol: 'tBTCF0:USTF0',
    start: 1700000000000,
    end: 1700100000000,
    limit: 100,
    sort: -1
);
$dsHist->content['items']; // array<DerivativeStatusHistory>
```

`derivativesStatusHistory()` is an alias for `derivativesStatus()`; both return the same service, so `history()` is reached the same way from either.

Notes:
- `keys`: list of derivative symbols (e.g., `tBTCF0:USTF0`). Only symbols listed by `pub:list:pair:futures` are valid.
- The history window is controlled via `start`, `end`, `limit`, `sort`; there is no `keys` filter since the symbol is already fixed in the path.



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

foreach ($rank->content['items'] as $entry) { // list<LeaderboardEntry>
    $entry->ranking;  // 1 is first
    $entry->username;
    $entry->value;    // what it measures depends on `key`
    $entry->mts;      // Carbon
}
```

Notes:
- `key`: ranking metric (consult Bitfinex docs; e.g., `pnl`, `vol`).
- `timeframe`: period (e.g., `1D`, `7D`).
- `section`: `hist` (history) or `last` (latest), default `hist`.
- The reference documents 10 fields but the API returns **13**. Only the four
  confirmed on the wire get accessors; `$entry->raw` holds the untouched row, so
  the undocumented tail stays reachable. `twitterHandle` is documented as a
  string and comes back null on every row checked.
- `byCurrency('USD')` asks for the global board, `tGLOBAL:USD`. Rankings are not
  a funding endpoint: a funding symbol answers HTTP 200 with an empty array.

```php
// Every pair quoted in USD, not a funding board
$rank = Bitfinex::public()->leaderboards('vol', '1M')->byCurrency('USD', limit: 10);
```

### Funding Statistics

```php
$stats = Bitfinex::public()->fundingStats()->byCurrency('USD', start: '2024-01-01', limit: 100, sort: -1);

foreach ($stats->content['items'] as $stat) { // list<FundingStat>
    $stat->frr;           // as sent: one 365th of the daily rate
    $stat->dailyRate();   // frr * 365
    $stat->annualRate();  // frr * 365 * 365, no compounding
    $stat->averagePeriod;
    $stat->fundingAmount;
    $stat->fundingAmountUsed;
    $stat->fundingBelowThreshold;
}
```

The FRR arrives divided by 365. Reading `$stat->frr` as a daily rate understates
it by that factor, which is why the scaling is a method rather than something you
are expected to remember.

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

Authenticated endpoints require valid API credentials and cover wallets, orders, positions, funding, account actions and Bitfinex Pay merchants. Set credentials via env (`BITFINEX_API_KEY`, `BITFINEX_API_SECRET`) or pass them explicitly, as shown in [Initialize](#initialize). Every example below assumes `$auth` was built that way.

Writes (submitting or cancelling orders, withdrawals, transfers, funding offers) move real funds or account state and are shown here but never executed as part of this guide; verify request shape against a local capture server first.

### Initialize

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

// Uses credentials from config/env
$auth = Bitfinex::authenticated();
```

```php
use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

$bf = new Bitfinex();
$auth = $bf->authenticated(new BitfinexCredentials('API_KEY', 'API_SECRET'));
```

Some endpoints (e.g. Bitfinex Pay) additionally require a session token:

```php
$auth = Bitfinex::authenticated()->generateToken();
```

### Wallets

```php
$resp = $auth->wallets()->get();
$resp->content['wallets']; // list<Wallet>
```

### Orders

```php
use EwertonDaniel\Bitfinex\Enums\BitfinexType;

// Retrieve: all symbols, or filtered by trading pair
$resp = $auth->orders()->retrieve();
$resp->content['orders']; // list<Order>

$resp = $auth->orders()->retrieve('XMRUSD');
$resp->content['orders'];

// History
$resp = $auth->orders()->history(limit: 50);
$resp->content['orders'];

// Trades for a given symbol (history)
$resp = $auth->orders()->tradesHistory(BitfinexType::TRADING, 'XMRUSD', limit: 50, sort: -1);
$resp->content['trades'];

// Ledgers for a currency; `category` and `wallet` are optional filters, there is no `sort`
$resp = $auth->orders()->ledgers(currency: 'USD', limit: 50);
$resp->content['ledgers']; // list<LedgerEntry>

// Omit the currency for every currency at once
$resp = $auth->orders()->ledgers(limit: 50);
```

Every write endpoint answers with a notification envelope. Its `STATUS` field is
what says whether the operation actually happened, and it can read `ERROR` under
an HTTP 200, so a refusal arrives as a `BitfinexNotificationException` rather than
as a response you have to inspect. The envelope itself is always available under
`content['notification']`.

```php
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexBatchException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;

// Submit
$resp = $auth->orders()->submit(
  type: OrderType::EXCHANGE_LIMIT,
  action: BitfinexAction::BUY,
  pair: 'XMRUSD',
  amount: 0.01,
  price: 140
);
$resp->content['order'];        // Order|null, the first entry of DATA
$resp->content['orders'];       // list<Order>, the full DATA list
$resp->content['notification']; // Notification: type, status, text, mts

// Update: `id` is required; `meta` is optional order metadata (aff_code, make_visible, protect_selfmatch)
$resp = $auth->orders()->update(id: 123456, price: 145, meta: ['aff_code' => 'partnerXYZ']);
$resp->content['order']; // Order|null

// Cancel
$resp = $auth->orders()->cancel(id: 123456);
$resp->content['order']; // Order|null

// A refusal carries the exchange's own words, and DATA survives on the exception:
// on a refused cancel it holds the order as it currently stands.
try {
    $auth->orders()->cancel(id: 123456);
} catch (BitfinexNotificationException $e) {
    $e->getMessage();             // "The Bitfinex API answered oc-req with status ERROR: Order not found."
    $e->notification->status;     // 'ERROR'
    $e->notification->data;       // raw DATA, or null when the API sent none
}
```

A submission can be **accepted and still leave no order**. Insufficient balance
takes two routes: an HTTP 500 with an error envelope, and an HTTP 200 whose
notification reads `SUCCESS` while the order's own `ORDER_STATUS` reads
`INSUFFICIENT BALANCE (U1)`. Same outcome, so both raise.

```php
use EwertonDaniel\Bitfinex\Exceptions\BitfinexOrderRejectedException;

try {
    $auth->orders()->submit(
      type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY,
      pair: 'XMRUSD', amount: 0.01, price: 140
    );
} catch (BitfinexOrderRejectedException $e) {
    $e->rejected;     // list<Order> — INSUFFICIENT BALANCE (U1), POSTONLY CANCELED, …
    $e->accepted();   // list<Order> — anything that did survive
}
```

`INSUFFICIENT BALANCE (G1)` does **not** raise: the reference is explicit that it
filled for the maximum affordable amount, so the caller is holding something. The
same goes for `PARTIALLY FILLED` and `RSN_BOOK_SLIP`. To classify a status
yourself, without matching strings:

```php
$order->isActive();    // sitting in the book, unfilled
$order->wasFilled();   // executed, fully or partially
$order->wasRejected(); // not in the book, nothing filled
```

```php
// Multi operations: each sub-operation carries its own status, and the outer one
// can read SUCCESS while an inner one failed. A partial failure raises, with both
// lists attached, because the accepted operations are live on the exchange.
$ops = [
  ['type' => 'LIMIT', 'symbol' => 'tXMRUSD', 'price' => '140', 'amount' => '0.01'],
  ['id' => 123456, 'type' => 'CANCEL'],
];

try {
    $resp = $auth->orders()->multi($ops);
    $resp->content['operations']; // list<Notification>, one per sub-operation
} catch (BitfinexBatchException $e) {
    $e->failures;    // list<Notification>, the rejected ones
    $e->succeeded(); // list<Notification>, the accepted ones — not rolled back
}

// Cancel multiple: the endpoint reports one status for the whole batch and lists
// only the orders it cancelled, so the ids are reconciled for you. An id in
// `missingIds` was not cancelled: already filled, already cancelled, or unknown.
$resp = $auth->orders()->cancelMultiple([111, 222, 333]);
$resp->content['orders'];       // list<Order>, the ones actually cancelled
$resp->content['cancelledIds']; // [111, 222]
$resp->content['missingIds'];   // [333]
```

### Positions

```php
// Margin info (e.g., 'base' or a specific key)
$resp = $auth->positions()->marginInfo('base');
$resp->content['margin'];

// Open positions
$resp = $auth->positions()->retrieve();
$resp->content['positions']; // list<Position>

// Claim: `id` is the position ID from retrieve(); amount is optional (partial claim)
$resp = $auth->positions()->claim(id: 123456);

// Increase: the endpoint accepts only symbol and amount, there is no price
$resp = $auth->positions()->increase('XMRUSD', 0.01);

// Increase info (what-if)
$resp = $auth->positions()->increaseInfo('XMRUSD', 0.01);
$resp->content['info'];

// History: `id` restricts the result to a single position, there is no `sort`
$resp = $auth->positions()->history(start: 1700000000000, end: 1700100000000, limit: 50);

// Snapshot / Audit
$resp = $auth->positions()->snapshot();
$resp = $auth->positions()->audit();

// Derivative collateral
$resp = $auth->positions()->setDerivativeCollateral('BTCUSD', 100.0);
$resp = $auth->positions()->derivativeCollateralLimits('BTCUSD');
```

### Funding

```php
// Active offers
$resp = $auth->funding()->activeOffers('USD');

foreach ($resp->content['offers'] as $offer) { // list<FundingOffer>
    $offer->symbol;   // 'fUSD'
    $offer->currency; // 'USD', prefix stripped
    $offer->amount;
    $offer->rate;     // per period; for an FRR offer this is the delta
    $offer->period;   // days
    $offer->status;   // ACTIVE, EXECUTED, PARTIALLY FILLED, CANCELED
}

// Writes answer with a notification envelope. A status other than SUCCESS
// raises BitfinexNotificationException instead of returning; on success the
// envelope is in content['notification'] and its DATA is mapped per endpoint.

// Submit / cancel offers: DATA is the offer as the exchange recorded it
$resp = $auth->funding()->submitOffer('USD', amount: 100.0, rate: 0.0002, period: 2);
$resp->content['offer'];        // FundingOffer|null
$resp->content['notification']; // Notification

$resp = $auth->funding()->cancelOffer(id: 123456);
$resp->content['offer'];        // FundingOffer|null, as it stands after cancellation

// Cancel-all: DATA is null, the outcome only exists in the free-form text
$resp = $auth->funding()->cancelAllOffers('USD');
$resp->content['notification']->text; // e.g. 'All (8) submitted for cancellation'

// Close a loan/credit: DATA is null
$resp = $auth->funding()->close(id: 123456);
$resp->content['notification'];

// Toggle auto-renew for a currency: DATA is [CURRENCY, PERIOD, RATE, THRESHOLD]
$resp = $auth->funding()->autoRenew(currency: 'USD', status: true);
$resp->content['autorenew'];    // array|null, e.g. ['USD', 2, 0, 350]

// Keep a credit or loan from being returned automatically when the position closes
$resp = $auth->funding()->keep(type: 'credit', ids: [123456]);
$resp->content['notification']->text; // e.g. 'Credit updated'

// Loans / Credits / Trades
$resp = $auth->funding()->loans('USD');
$resp->content['loans']; // list<FundingLoan>

// A credit is a loan that is financing a position: same layout, plus one field.
$resp = $auth->funding()->credits('USD');
foreach ($resp->content['credits'] as $credit) { // list<FundingCredit>
    $credit->positionPair; // 'tBTCUST' — the field a FundingLoan does not have
    $credit->rateType;     // FIXED, or VAR for the Flash Return Rate
    $credit->openedAt;
    $credit->lastPayoutAt;
}

$resp = $auth->funding()->trades('USD', limit: 50, sort: -1);
$resp->content['trades']; // list<FundingTrade>: offerId, amount, rate, period

// History and info
$resp = $auth->funding()->offersHistory('USD', limit: 50);
$resp = $auth->funding()->loansHistory('USD', limit: 50);
$resp = $auth->funding()->creditsHistory('USD', limit: 50);
$resp = $auth->funding()->info('funding.size');
```

### Account Actions

```php
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;

// User info
$resp = $auth->accountAction()->userInfo();
$resp->content['user'];

// Deposit address: single, or a paginated list for a method
$resp = $auth->accountAction()->depositAddress(BitfinexWalletType::EXCHANGE, 'crypto');
$resp->content['address'];

$resp = $auth->accountAction()->depositAddressList('crypto', page: 1, pageSize: 20);
$resp->content['addresses']['items'];

// Movements: mixed, deposits only, withdrawals only
$resp = $auth->accountAction()->movements('BTC', start: '2024-01-01', end: '2024-12-31', limit: 200);
$resp->content['movements']; // array<Movement>

$resp = $auth->accountAction()->depositHistory('BTC', limit: 100);
$resp->content['deposits']; // array<Movement>

$resp = $auth->accountAction()->withdrawalHistory('BTC', limit: 100);
$resp->content['withdrawals']; // array<Movement>

$resp = $auth->accountAction()->movementInfo(1234567890);
$resp->content['movement']; // Movement

// Withdrawal: the currency is implied by `method` (e.g. 'tetheruse'), not a separate argument.
// A refused withdrawal raises BitfinexNotificationException; on success the
// notification's DATA is mapped to a Withdrawal entity.
$resp = $auth->accountAction()->withdrawal(
  walletType: BitfinexWalletType::EXCHANGE,
  method: 'tetheruse',
  amount: 50.0,
  options: ['address' => '0x0000000000000000000000000000000000dEaD']
);
$resp->content['withdrawal'];         // Withdrawal|null: id, method, wallet, amount, fee
$resp->content['notification'];       // Notification

// Transfer between wallets. A refused transfer raises BitfinexNotificationException
// rather than returning a response whose `status` reads 'ERROR'.
$resp = $auth->accountAction()->transferBetweenWallets(
  from: BitfinexWalletType::EXCHANGE,
  to: BitfinexWalletType::MARGIN,
  currency: 'USD',
  amount: 50.0
);
$resp->content['transferred'];  // raw DATA of the notification
$resp->content['notification']; // Notification

// Alerts
$resp = $auth->accountAction()->alertSet('XMRUSD', 250); // create
$resp = $auth->accountAction()->alertList('price');
$resp = $auth->accountAction()->alertDelete('XMRUSD', 250); // delete

// User settings: `keys` is required for both read and delete
$resp = $auth->accountAction()->userSettingsWrite(['api:my_setting' => 'value']);
$resp = $auth->accountAction()->userSettingsRead(['api:my_setting']);
$resp = $auth->accountAction()->userSettingsDelete(['api:my_setting']);
```

### Merchants (Bitfinex Pay)

```php
// Invoices
$resp = $auth->merchants()->submitInvoice(['wallet' => 'exchange', 'currency' => 'USD', 'amount' => '10.00']);
$resp->content['invoice'];

$resp = $auth->merchants()->submitPostInvoice(['wallet' => 'exchange', 'currency' => 'USD', 'amount' => '10.00']);
$resp = $auth->merchants()->invoiceList(['status' => 'pending']);
$resp = $auth->merchants()->invoiceListPaginated(page: 1, pageSize: 30);
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
