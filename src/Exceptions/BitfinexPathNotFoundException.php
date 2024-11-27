<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

/**
 * Class BitfinexPathNotFoundException
 *
 * Exception thrown when a requested API path is not found in the configuration.
 * Extends the base BitfinexException class.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexPathNotFoundException extends BitfinexException
{
    /**
     * @var string The API path that was not found.
     */
    private string $path;

    /**
     * @var array|null Additional debug data for context.
     */
    private ?array $debugData;

    /**
     * Constructs the exception with a custom message and optional debug data.
     *
     * @param  string  $path  The API path that was not found.
     * @param  array|null  $debugData  Additional debug data (default: null).
     * @param  int  $code  The exception code (default: 0).
     */
    public function __construct(string $path, ?array $debugData = null, int $code = 0)
    {
        $this->path = $path;
        $this->debugData = $debugData;

        $message = sprintf('Path not found: %s', $path);
        if ($debugData) {
            $message .= sprintf(' | Debug Data: %s', json_encode($debugData));
        }

        parent::__construct($message, $code);
    }

    /**
     * Gets the API path that caused the exception.
     *
     * @return string The missing API path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the debug data associated with the exception.
     *
     * @return array|null The debug data, or null if none was provided.
     */
    public function getDebugData(): ?array
    {
        return $this->debugData;
    }

    /**
     * Converts the exception into an associative array for structured logging.
     *
     * @return array An array containing exception details.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['path'] = $this->path;
        $array['debugData'] = $this->debugData;

        return $array;
    }

    /**
     * Converts the exception to a readable string for debugging.
     *
     * @return string The exception details as a string.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s: [%d]: %s\nPath: %s\nDebug Data: %s\nIn %s on line %d\nTrace: %s",
            __CLASS__,
            $this->code,
            $this->message,
            $this->path,
            $this->debugData ? json_encode($this->debugData) : 'None',
            $this->file,
            $this->line,
            $this->getTraceAsString()
        );
    }
}
