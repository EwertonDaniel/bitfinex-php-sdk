<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;

/**
 * Class BitfinexException
 *
 * Base exception class for Bitfinex-related errors.
 * Implements PSR-18's `ClientExceptionInterface` for HTTP client compatibility.
 *
 * Key Features:
 * - Extends the standard Exception class to include Bitfinex-specific error handling.
 * - Implements methods for structured debugging and logging.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexException extends Exception implements ClientExceptionInterface
{
    /**
     * Constructs the BitfinexException.
     *
     * @param  string  $message  The error message.
     * @param  int  $code  The error code (default: 0).
     * @param  Throwable|null  $previous  Optional previous exception for chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Converts the exception to a string representation.
     *
     * @return string The exception details as a formatted string.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s: [%d]: %s in %s on line %d\nTrace: %s",
            __CLASS__,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->getTraceAsString()
        );
    }

    /**
     * Converts the exception into an associative array for structured logging or debugging.
     *
     * @return array An array containing exception details.
     */
    public function toArray(): array
    {
        return [
            'class' => __CLASS__,
            'code' => $this->code,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->getTrace(),
            'previous' => $this->getPrevious()?->getMessage(),
        ];
    }
}
