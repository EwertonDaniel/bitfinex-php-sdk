<?php

declare(strict_types=1);

namespace Tests;

use EwertonDaniel\Bitfinex\BitfinexServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Set up the environment configuration for testing.
     *
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        // Set fake API credentials for testing
        config()->set('bitfinex.api_key', 'key');
        config()->set('bitfinex.api_secret', 'key');
    }

    /**
     * Get the package providers that should be loaded for testing.
     *
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            BitfinexServiceProvider::class,
        ];
    }
}
