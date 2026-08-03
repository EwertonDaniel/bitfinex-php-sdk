<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\ValueObjects;

use EwertonDaniel\Bitfinex\Contracts\NonceProvider;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;

/**
 * Class LockedFileNonceProvider
 *
 * A nonce counter shared across processes on one machine, kept in a file guarded
 * by an exclusive `flock`.
 *
 * This is the dependency-free option for the common case: several PHP-FPM
 * workers or queue consumers on the same host, all signing with one API key. For
 * workers spread across machines, back the counter with something they all
 * reach (Redis `INCR`, a database sequence) and implement `NonceProvider`
 * directly.
 *
 * The value is still microseconds since epoch, so it stays compatible with a
 * key that was previously used with the in-process counter: the file only
 * raises the floor, it never restarts the sequence.
 *
 * One file per API key. Two keys sharing a file still work, but they burn
 * through the sequence twice as fast for no reason.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/docs/requirements-and-limitations
 */
class LockedFileNonceProvider implements NonceProvider
{
    /**
     * @param  string  $path  File holding the counter. Created if absent; its
     *                        directory must already exist and be writable.
     */
    public function __construct(private readonly string $path) {}

    /**
     * @throws BitfinexException When the file cannot be opened or locked, or when
     *                           the sequence would pass the documented ceiling.
     */
    public function next(): int
    {
        // Checked up front so the failure names what is actually wrong. Letting
        // fopen() fail instead produces a bare "failed to open stream", and a
        // nonce counter that cannot persist has to fail loudly: silently falling
        // back to an in-process value is exactly the collision this class exists
        // to prevent.
        $directory = dirname($this->path);

        if (! is_dir($directory) || ! is_writable($directory)) {
            throw new BitfinexException("The nonce file's directory is missing or not writable: $directory.");
        }

        $handle = @fopen($this->path, 'c+');

        if ($handle === false) {
            throw new BitfinexException("The nonce file could not be opened for writing: $this->path.");
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new BitfinexException("The nonce file could not be locked: $this->path.");
            }

            $stored = (int) stream_get_contents($handle);

            // Clock time is the floor, so a key that was used with the
            // in-process counter keeps moving forward rather than rewinding.
            $nonce = max((int) round(microtime(true) * 1_000_000), $stored + 1);

            if ($nonce > self::MAX_NONCE) {
                throw new BitfinexException(
                    'The nonce sequence passed the documented ceiling of '.self::MAX_NONCE.'.'
                );
            }

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, (string) $nonce);
            fflush($handle);

            return $nonce;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
