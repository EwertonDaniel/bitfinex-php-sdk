<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Builders;

/**
 * Class RequestQueryParamsBuilder
 *
 * Manages query parameters for an HTTP request. Provides methods to add, reset,
 * and retrieve query parameters. Handles merging of parameters for flexibility in
 * building requests.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class RequestQueryParamsBuilder
{
    private array $queryParams = [];

    /**
     * Adds or updates a single query parameter.
     *
     * @param  string  $key  Parameter name.
     * @param  mixed  $value  Parameter value.
     */
    final public function __set(string $key, mixed $value): void
    {
        $this->queryParams[$key] = $value;
    }

    /**
     * Merges custom query parameters with the current parameters.
     *
     * @param  array  $queryParams  Associative array of query parameters.
     */
    final public function setQueryParams(array $queryParams): static
    {
        $this->queryParams = array_merge($this->queryParams, $queryParams);

        return $this;
    }

    /**
     * Resets query parameters to an empty state.
     */
    final public function reset(): static
    {
        $this->queryParams = [];

        return $this;
    }

    /**
     * Retrieves all query parameters.
     *
     * @return array Associative array of query parameters.
     */
    final public function get(): array
    {
        return $this->queryParams;
    }
}
