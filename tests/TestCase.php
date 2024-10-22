<?php

namespace EwertonDaniel\Bitfinex\Tests;

use EwertonDaniel\Bitfinex\BitfinexServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        Config::set('bitfinex.api_key', 'key');
        Config::set('bitfinex.api_secret', 'key');

        parent::getEnvironmentSetUp($app);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->withFacades();
    }

    protected function getPackageProviders($app): array
    {
        return [BitfinexServiceProvider::class];
    }
}
