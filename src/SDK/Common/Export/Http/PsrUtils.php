<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function array_filter;
use function array_map;
use function count;
use ErrorException;
use function function_exists;
use function in_array;
use function inflate_get_read_len;
use function inflate_get_status;
use LogicException;
use function max;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use function rand;
use function restore_error_handler;
use RuntimeException;
use function set_error_handler;
use function sprintf;
use function strlen;
use function strtotime;
use function substr;
use Throwable;
use function time;
use function trim;
use UnexpectedValueException;

/**
 * @internal
 */
final class PsrUtils
{
    private const READ_CHUNK_SIZE = 8192;
    private const INFLATE_CHUNK_SIZE = 512;

    /**
     * @param int $retry zero-indexed attempt number
     * @param int $retryDelay initial delay in milliseconds
     * @param ResponseInterface|null $response response of failed request
     * @return float delay in seconds
     */
    public static function retryDelay(int $retry, int $retryDelay, ?ResponseInterface $response = null): float
    {
        $delay = $retryDelay << $retry;
        $delay = rand($delay >> 1, $delay) / 1000;

        return max($delay, self::parseRetryAfter($response));
    }

    private static function parseRetryAfter(?ResponseInterface $response): int
    {
        if (!$response || !$retryAfter = $response->getHeaderLine('Retry-After')) {
            return 0;
        }

        $retryAfter = trim($retryAfter, " \t");
        if ($retryAfter === (string) (int) $retryAfter) {
            return (int) $retryAfter;
        }

        if (($time = strtotime($retryAfter)) !== false) {
            return $time - time();
        }

        return 0;
    }

    /**
     * @param list<string> $encodings
     * @param array<int, string>|null $appliedEncodings
     * @psalm-suppress PossiblyInvalidArrayOffset
     */
    public static function encode(string $value, array $encodings, ?array &$appliedEncodings = null): string
    {
        for ($i = 0, $n = count($encodings); $i < $n; $i++) {
            if (!$encoder = self::encoder($encodings[$i])) {
                unset($encodings[$i]);

                continue;
            }

            try {
                $value = $encoder($value);
            } catch (Throwable) {
                unset($encodings[$i]);
            }
        }

        $appliedEncodings = $encodings;

        return $value;
    }

    /**
     * Reads a response body, decoding it with the given content encodings, without exceeding `$maxSize` bytes.
     *
     * The limit applies to the body as received (compressed, so a body of incompressible data close to the
     * limit is rejected too) and to the output of every decoding step, so a small compressed
     * body can't expand into an unbounded amount of memory. Zlib output is checked after every 512 bytes of
     * input, which is at most about 0.5 MiB. Only gzip is decoded, as the specification only requires gzip
     * and none.
     *
     * The PSR-18 client has usually buffered the response already, so this bounds what the transport keeps
     * and decodes, not what the client received.
     *
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     *
     * @param list<string> $encodings content encodings in the order they were applied, in lower case
     * @param positive-int $maxSize maximum size in bytes
     * @throws ResponseBodyTooLargeException if the limit is exceeded
     * @throws UnexpectedValueException if the body can't be decoded
     * @throws RuntimeException if the body can't be read
     */
    public static function readBody(ResponseInterface $response, array $encodings, int $maxSize): string
    {
        $stream = $response->getBody();
        $size = $stream->getSize();
        if ($size !== null && $size > $maxSize) {
            throw new ResponseBodyTooLargeException($maxSize);
        }

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $value = '';
        $length = 0;
        while (true) {
            $chunk = $stream->read(self::READ_CHUNK_SIZE);
            if ($chunk === '') {
                if (!$stream->eof()) {
                    throw new RuntimeException('Failed to read the response body');
                }

                break;
            }
            $length += strlen($chunk);
            if ($length > $maxSize) {
                throw new ResponseBodyTooLargeException($maxSize);
            }
            $value .= $chunk;
        }

        if ($value === '') {
            return $value;
        }

        for ($i = count($encodings); --$i >= 0;) {
            if ($encodings[$i] === 'identity') {
                continue;
            }

            $value = self::decodeWithLimit($value, $encodings[$i], $maxSize);
        }

        return $value;
    }

    private static function decodeWithLimit(string $value, string $encoding, int $maxSize): string
    {
        // The specification only requires gzip and none for OTLP/HTTP.
        // x-gzip is an alias of gzip.
        if (!in_array($encoding, [TransportFactoryInterface::COMPRESSION_GZIP, 'x-gzip'], true)) {
            throw new UnexpectedValueException(sprintf('Not supported decompression encoding "%s"', $encoding));
        }
        if (!function_exists('inflate_init')) {
            throw new UnexpectedValueException('Decoding a gzip response body requires the zlib extension');
        }

        $context = self::withoutWarnings(static fn () => inflate_init(ZLIB_ENCODING_GZIP), $error);
        if ($context === false) {
            throw new UnexpectedValueException('Failed to initialize gzip decompression: ' . $error);
        }

        $output = '';
        $outputLength = 0;
        $n = strlen($value);
        for ($offset = 0; $offset < $n; $offset += self::INFLATE_CHUNK_SIZE) {
            $chunk = self::withoutWarnings(
                static fn () => inflate_add(
                    $context,
                    substr($value, $offset, self::INFLATE_CHUNK_SIZE),
                    $offset + self::INFLATE_CHUNK_SIZE >= $n ? ZLIB_FINISH : ZLIB_SYNC_FLUSH,
                ),
                $error,
            );
            if ($chunk === false) {
                throw new UnexpectedValueException('Failed to decode gzip response body: ' . $error);
            }
            $outputLength += strlen($chunk);
            if ($outputLength > $maxSize) {
                throw new ResponseBodyTooLargeException($maxSize);
            }
            $output .= $chunk;

            if (inflate_get_status($context) === ZLIB_STREAM_END) {
                // Data after the end of the gzip stream, such as a second gzip member, is not decoded, so it
                // would escape the limit and be dropped silently.
                if (inflate_get_read_len($context) < $n) {
                    throw new UnexpectedValueException('Unexpected data after the end of the gzip response body');
                }

                return $output;
            }
        }

        throw new UnexpectedValueException('Truncated gzip response body');
    }

    /**
     * Calls `$callable`, keeping its warnings out of the application's error handler, and returns the message
     * of the last one in `$error`.
     *
     * @template T
     * @param callable(): T $callable
     * @return T
     */
    private static function withoutWarnings(callable $callable, ?string &$error = null): mixed
    {
        $error = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$error): bool {
            $error = $errstr;

            return true;
        });

        try {
            return $callable();
        } finally {
            restore_error_handler();
            $error ??= 'unknown error';
        }
    }

    /**
     * Resolve an array or CSV of compression types to a list
     */
    public static function compression($compression): array
    {
        if (is_array($compression)) {
            return $compression;
        }
        if (!$compression) {
            return [];
        }
        if (!str_contains((string) $compression, ',')) {
            return [$compression];
        }

        return array_map(trim(...), explode(',', (string) $compression));
    }

    private static function encoder(string $encoding): ?callable
    {
        static $encoders;

        /** @noinspection SpellCheckingInspection */
        $encoders ??= array_map(self::throwOnErrorOrFalse(...), array_filter([
            TransportFactoryInterface::COMPRESSION_GZIP => 'gzencode',
            TransportFactoryInterface::COMPRESSION_DEFLATE => 'gzcompress',
            TransportFactoryInterface::COMPRESSION_BROTLI => 'brotli_compress',
        ], function_exists(...)));

        return $encoders[$encoding] ?? null;
    }

    private static function throwOnErrorOrFalse(callable $callable): callable
    {
        return static function (...$args) use ($callable) {
            set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

            try {
                $result = $callable(...$args);
            } finally {
                restore_error_handler();
            }

            /** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
            if ($result === false) {
                throw new LogicException();
            }

            /** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
            return $result;
        };
    }
}
