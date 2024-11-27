<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Exceptions;

/**
 * Class BitfinexFileNotFoundException
 *
 * Exception thrown when a required file is not found in the specified path.
 * This exception provides additional context about the missing file.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexFileNotFoundException extends BitfinexException
{
    /**
     * @var string The path of the file that was not found.
     */
    private string $filePath;

    /**
     * Constructs the exception with a custom message and file path.
     *
     * @param  string  $filePath  The path of the missing file.
     * @param  int  $code  The exception code (default: 0).
     */
    public function __construct(string $filePath, int $code = 0)
    {
        $this->filePath = $filePath;
        $message = sprintf('File not found: %s', $filePath);
        parent::__construct($message, $code);
    }

    /**
     * Gets the path of the file that caused the exception.
     *
     * @return string The missing file path.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Converts the exception into an associative array for structured logging.
     *
     * @return array An array containing exception details.
     */
    public function toArray(): array
    {
        return [
            'class' => __CLASS__,
            'code' => $this->code,
            'message' => $this->message,
            'filePath' => $this->filePath,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->getTrace(),
        ];
    }

    /**
     * Converts the exception into a readable string format for debugging.
     *
     * @return string The exception details as a string.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s: [%d]: %s\nFile Path: %s\nIn %s on line %d\nTrace: %s",
            __CLASS__,
            $this->code,
            $this->message,
            $this->filePath,
            $this->file,
            $this->line,
            $this->getTraceAsString()
        );
    }
}
