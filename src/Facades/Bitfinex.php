<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Bitfinex
 *
 * This class serves as a Laravel Facade for the Bitfinex service, providing a static interface
 * to interact with the `bitfinex` binding in the Laravel service container.
 *
 * Facades in Laravel simplify access to underlying classes that are bound in the container,
 * offering a cleaner and more readable syntax for common operations.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 *
 * @since 2024-10-22
 * @see https://laravel.com/docs/facades For more about Laravel Facades.
 */
class Bitfinex extends Facade
{
    /**
     * Get the registered name of the component in the Laravel container.
     *
     * This method defines the service container binding that the facade resolves to.
     * In this case, it resolves to the `bitfinex` binding.
     *
     * @return string The container binding key for the Bitfinex service.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'bitfinex';
    }
}
