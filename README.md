# Bitfinex PHP SDK

![Packagist Version](https://img.shields.io/packagist/v/ewertondaniel/bitfinex-php-sdk)
![Packagist License](https://img.shields.io/packagist/l/ewertondaniel/bitfinex-php-sdk)

## Installation

Install via Composer:

```bash
composer require ewertondaniel/bitfinex-php-sdk
```

## Quick Start

Public example:

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$status = Bitfinex::public()->platformStatus();
echo $status->content->status; // "operative" or "maintenance"
```

Authenticated example (Laravel, via config/env):

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$auth = Bitfinex::authenticated()->generateToken();
$wallets = $auth->wallets()->get();
foreach ($wallets->content['wallets'] as $wallet) {
    // ...
}
```

More examples in the [usage guide](docs/USAGE.md).

## Overview

The **Bitfinex PHP SDK** is a lightweight PHP library designed to simplify interaction with the Bitfinex REST API. This SDK allows developers to easily integrate advanced cryptocurrency trading functionalities into PHP applications. Whether building automated trading bots, monitoring market trends, or developing custom trading platforms, this SDK makes the integration with Bitfinex straightforward and efficient.

## Features

- Comprehensive public endpoints: status, tickers (incl. history), trades, book, candles, stats, leaderboards, derivatives status (+history), liquidations, configs, funding stats; calc endpoints: market average price, FX rate.
- Authenticated endpoints: wallets, orders, positions, funding, account actions, merchants (Bitfinex Pay).
- Laravel integration (10–12): ServiceProvider, Facade and DI/container.
- HTTP via Guzzle with builders for headers/query/body.

## API Documentation

### What is the Bitfinex API?

The **Bitfinex API** provides access to the full suite of features available on the Bitfinex platform. It allows users to retrieve market data, manage account details, place orders, and more. Designed for speed, the API is optimized to support high-frequency trading and low-latency data streaming, making it an essential tool for advanced trading strategies.

### REST vs WebSocket

- **REST**: Best for historical data and authenticated operations but subject to rate limits.
- **WebSocket**: Ideal for real-time data streaming and high-speed market interactions. WebSocket connections allow for up to 5 authenticated connections per 15 seconds and up to 20 public connections per minute.

For detailed information on endpoints, methods, and usage guidelines, refer to the [Bitfinex API Documentation](https://docs.bitfinex.com/).

### Important Guidelines

- Use numeric error codes to handle exceptions.
- All timestamps are in UNIX format, expressed in milliseconds.
- Trading pair symbols use a `t` prefix (e.g., `tBTCUSD` for Bitcoin to USD).

### Legal Disclaimer

Any use of the Bitfinex API is subject to the [API Terms of Service](https://www.bitfinex.com/legal/api). All API keys and interactions are at your own risk and expense. iFinex Inc. is not responsible for any losses or damages resulting from the use of this SDK or the Bitfinex API.

## Available Endpoints (Public)

### Market Data
- Tickers (GET) — https://docs.bitfinex.com/reference/rest-public-tickers
- Ticker (GET) — https://docs.bitfinex.com/reference/rest-public-ticker
- Ticker History (GET) — https://docs.bitfinex.com/reference/rest-public-ticker-history
- Trades (GET) — https://docs.bitfinex.com/reference/rest-public-trades
- Book (GET) — https://docs.bitfinex.com/reference/rest-public-order-books
- Candles (GET) — https://docs.bitfinex.com/reference/rest-public-candles
- Stats (GET) — https://docs.bitfinex.com/reference/rest-public-stats

### Reference / Meta
- Platform Status (GET) — https://docs.bitfinex.com/reference/rest-public-platform-status
- Configs (GET) — https://docs.bitfinex.com/reference/rest-public-conf

### Risk / Monitoring
- Liquidations (GET) — https://docs.bitfinex.com/reference/rest-public-liquidations
- Leaderboards (GET) — https://docs.bitfinex.com/reference/rest-public-rankings
- Derivatives Status (GET) — https://docs.bitfinex.com/reference/rest-public-derivatives-status
- Derivatives Status History (GET) — https://docs.bitfinex.com/reference/rest-public-derivatives-status-history
- Funding Statistics (GET) — https://docs.bitfinex.com/reference/rest-public-funding-stats

### Calculations
- Market Average Price (POST) — https://docs.bitfinex.com/reference/rest-public-market-average-price
- Foreign Exchange Rate (POST) — https://docs.bitfinex.com/reference/rest-public-foreign-exchange-rate
## Available Endpoints (Authenticated)



- Wallets — https://docs.bitfinex.com/reference/rest-auth-wallets
  - Wallets (GET)

- Orders — https://docs.bitfinex.com/reference/rest-auth-retrieve-orders
  - Retrieve Orders / Retrieve Orders by Symbol (POST)
  - Submit Order / Update Order / Cancel Order (POST)
  - Order Multi Op / Cancel Multiple (POST)
  - Orders History (POST)
  - Order Trades / Trades History (POST)
  - Ledgers (POST)

- Positions — https://docs.bitfinex.com/reference/rest-auth-positions
  - Margin Info / Retrieve Positions (POST)
  - Claim Position / Increase Position / Increase Position Info (POST)
  - Positions History / Snapshot / Audit (POST)
  - Derivative Position Collateral / Collateral Limits (POST)

- Funding — https://docs.bitfinex.com/reference/rest-auth-funding-offers
  - Active Funding Offers (POST)
  - Submit / Cancel / Cancel All Funding Offers (POST)
  - Funding Close / Auto Renew / Keep (POST)
  - Funding Offers / Loans / Credits / Trades (history) (POST)
  - Funding Info (POST)

- Account Actions — https://docs.bitfinex.com/reference/rest-auth-info-user
  - User Info / Summary / Login History / Key Permissions (POST)
  - Generate Token / Changelog / Transfer Between Wallets (POST)
  - Deposit Address / Deposit Addresses / Generate Invoice (POST)
  - Withdrawal / Movement Info / Movements (POST)
  - Alert List / Set / Delete (POST)
  - Balance Available for Orders/Offers (POST)
  - User Settings Write / Read / Delete (POST)

- Merchants (Bitfinex Pay) — https://docs.bitfinex.com/reference/rest-auth-ext-pay-invoice-create
  - Submit Invoice / Submit POS Invoice (POST)
  - Invoice List (+ paginated) (POST)
  - Invoice Count Stats / Earnings Stats (POST)
  - Complete / Expire Invoice (POST)
  - Currency Conversion: List / Add / Remove (POST)
  - Merchant Limit (POST)
  - Merchant Settings: Write / Write Batch / Read / List (POST)
  - Deposits List / Unlinked Deposits List (POST)


## Architecture (High‑Level)

- Transformers (Strategy + Factory):
  - `PublicBitfinexResponse` delegates mappings to classes in `Http/Responses/Public/Transformers` via `TransformerFactory`.
- Configs (Strategy):
  - The `conf` endpoint uses `ConfigsTransformer` with strategies by mode (map/list/info:*).
- Builders:
  - `RequestBuilder` offers a fluent API for headers/query/body; for GET methods, body is not sent.
- Adapters (Template Method):
  - `JsonAdapter::transform()` is final; `UrlAdapter`/`PathAdapter` only define `getFilePath()`.

These patterns keep low coupling, testability, and safe SDK evolution.

## Contributing

Contributions are welcome. See the [contribution guide](docs/CONTRIBUTING.md) for details.

## Testing & Tooling

- Run unit tests (Pest):
  - `vendor/bin/pest`
  - Public subset: `vendor/bin/pest tests/Unit/Public`

- Lint & static analysis:
  - `composer lint` (Pint + PHPStan)

- Format:
  - `composer format` (Pint)


## License

This package is licensed under the [MIT License](LICENSE).

## Author

**Ewerton Daniel** - [contact@ewertondaniel.work](mailto:contact@ewertondaniel.work)

## Support

If this project helps you, consider sending some Monero:

`89ynYeog7vt6san1FENHDQhn4RnG9sR2f2jj5DSpgY6q18sjQcyRoYYEpFEFQDaJ3ajjRXaMnikm1P2xKPK4jEwsMeK5o6Q`