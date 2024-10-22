<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex;

use EwertonDaniel\Bitfinex\Facades\Bitfinex;
use Illuminate\Support\ServiceProvider;

class BitfinexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bitfinex.php', 'bitfinex');

        $this->app->singleton('bitfinex', function ($app) {
            return new Bitfinex;
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/bitfinex.php' => config_path('bitfinex.php'),
        ], 'config');
    }
}
