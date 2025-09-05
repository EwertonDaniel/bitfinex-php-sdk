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
