<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

/**
 * Class BitfinexUrlNotFoundException
 *
 * Exception thrown when a requested URL type is not found in the configuration.
 * Extends the base BitfinexException class.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexUrlNotFoundException extends BitfinexException
{
    /**
     * @var string The type of URL that was not found.
     */
    private string $urlType;

    /**
     * Constructs the exception with a custom message and error code.
     *
     * @param  string  $urlType  The type of URL that was not found.
     * @param  int  $code  The exception code (default: 0).
     */
    public function __construct(string $urlType, int $code = 0)
    {
        $this->urlType = $urlType;
        $message = sprintf('URL not found for type: %s', $urlType);
        parent::__construct($message, $code);
    }

    /**
     * Gets the type of URL that caused the exception.
     *
     * @return string The missing URL type.
     */
    public function getUrlType(): string
    {
        return $this->urlType;
    }

    /**
     * Converts the exception into an associative array for structured logging.
     *
     * @return array An array containing exception details.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['urlType'] = $this->urlType;

        return $array;
    }
}
