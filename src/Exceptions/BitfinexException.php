<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;

class BitfinexException extends Exception implements ClientExceptionInterface
{
    public function __construct(
        string     $message = '',
        int        $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

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
}
