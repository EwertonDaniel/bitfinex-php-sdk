# Bitfinex PHP SDK

![Packagist Version](https://img.shields.io/packagist/v/ewertondaniel/bitfinex-php-sdk)
![Packagist License](https://img.shields.io/packagist/l/ewertondaniel/bitfinex-php-sdk)

## Overview

The **Bitfinex PHP SDK** is a lightweight PHP library that simplifies interaction with the Bitfinex REST API. It enables developers to integrate
advanced cryptocurrency trading functionalities into their PHP applications with ease. Whether you are developing automated trading bots, monitoring
the market, or building a custom trading platform, this SDK will help streamline your integration with Bitfinex.

## Features

- Retrieve real-time market data.
- Access historical ticker data.
- Place orders, manage trades, and handle trading operations.
- Perform public and authenticated API calls.
- Built-in support for Laravel 9, 10, and 11.
- Uses Guzzle for HTTP requests.

## Requirements

- PHP 8.1 or higher.
- Guzzle HTTP client library.
- Laravel 9, 10, or 11 (optional for Laravel-specific features).

## Installation

You can install the package using Composer:

```bash
composer require ewertondaniel/bitfinex-php-sdk
```

The SDK provides a facade to simplify API interaction. Below are some examples of how to use the library:

1. Retrieve Bitfinex Platform Status

```php
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

$response = Bitfinex::public()->platformStatus();;
echo $response->contents->status; // Output: operative or maintenance
```
