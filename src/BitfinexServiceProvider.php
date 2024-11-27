<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex;

use Illuminate\Support\ServiceProvider;

/**
 * Class BitfinexServiceProvider
 *
 * Provides integration with the Bitfinex API by registering configuration and bindings.
 * This service provider handles the setup of the `Bitfinex` class as a singleton
 * in the Laravel service container and publishes the configuration file for customization.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexServiceProvider extends ServiceProvider
{
    /**
     * Registers bindings and configurations for the Bitfinex API integration.
     *
     * This method merges the package's default configuration with the application's
     * existing configuration and binds the `Bitfinex` class as a singleton.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bitfinex.php',
            'bitfinex'
        );

        $this->app->singleton('bitfinex', function ($app) {
            return new Bitfinex;
        });
    }

    /**
     * Bootstraps services and publishes resources.
     *
     * This method makes the configuration file publishable so that developers can
     * customize it for their application.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/bitfinex.php' => config_path('bitfinex.php'),
        ], 'config');
    }
}
