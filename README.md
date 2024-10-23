# Bitfinex PHP SDK

![Packagist Version](https://img.shields.io/packagist/v/ewertondaniel/bitfinex-php-sdk)
![Packagist License](https://img.shields.io/packagist/l/ewertondaniel/bitfinex-php-sdk)

## Installation

To install the package using Composer, run the following command:

```
composer require ewertondaniel/bitfinex-php-sdk
```

## Overview

The **Bitfinex PHP SDK** is a lightweight PHP library designed to simplify interaction with the Bitfinex REST API. This SDK allows developers to easily integrate advanced cryptocurrency trading functionalities into PHP applications. Whether building automated trading bots, monitoring market trends, or developing custom trading platforms, this SDK makes the integration with Bitfinex straightforward and efficient.

## Features

- Retrieve real-time market data.
- Access historical ticker data.
- Place orders, manage trades, and handle trading operations.
- Perform both public and authenticated API calls.
- Full support for Laravel 9, 10, and 11.
- Utilizes Guzzle for HTTP requests.

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

## Available Endpoints

Currently, the following public REST endpoints are available for use:

- **Platform Status** (GET)
- **Ticker** (GET)
- **Tickers** (GET)
- **Tickers History** (GET)
- **Trades** (GET)
- **Foreign Exchange Rate** (POST)

### Upcoming Endpoints

In a future development stage, the following additional public REST endpoints and calculation endpoints will be implemented:

- **Book** (GET)
- **Stats** (GET)
- **Candles** (GET)
- **Derivatives Status** (GET)
- **Derivatives Status History** (GET)
- **Liquidations** (GET)
- **Leaderboards** (GET)
- **Funding Statistics** (GET)
- **Configs** (GET)
- **Virtual Asset Service Providers** (GET)

#### Calculation Endpoints

- **Market Average Price** (POST)

Stay tuned for updates as these endpoints become available!

## License

This package is licensed under the [MIT License](LICENSE).

## Author

**Ewerton Daniel** - [contact@ewertondaniel.work](mailto:github@ewertondaniel.work)
