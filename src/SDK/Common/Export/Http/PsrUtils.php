<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function array_filter;
use function array_map;
use function count;
use ErrorException;
use function function_exists;
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
use function strcasecmp;
use function strlen;
use function strtolower;
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
     * The limit applies to the body as received and to the output of every decoding step, so a small compressed
     * body can't expand into an unbounded amount of memory. Zlib output is checked after every 512 bytes of
     * input, which is at most about 0.5 MiB. Brotli can only be checked after decoding.
     *
     * The PSR-18 client has usually buffered the response already, so this bounds what the transport keeps
     * and decodes, not what the client received.
     *
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     *
     * @param list<string> $encodings
     * @param positive-int $maxSize maximum size in bytes
     * @throws ResponseBodyTooLargeException if the limit is exceeded
     * @throws UnexpectedValueException if the body can't be decoded
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
            if (strcasecmp($encodings[$i], 'identity') === 0) {
                continue;
            }

            $value = self::decodeWithLimit($value, $encodings[$i], $maxSize);
        }

        return $value;
    }

    private static function decodeWithLimit(string $value, string $encoding, int $maxSize): string
    {
        $zlibEncoding = match (strtolower($encoding)) {
            TransportFactoryInterface::COMPRESSION_GZIP => ZLIB_ENCODING_GZIP,
            TransportFactoryInterface::COMPRESSION_DEFLATE => ZLIB_ENCODING_DEFLATE,
            default => null,
        };

        if ($zlibEncoding !== null && function_exists('inflate_init')) {
            $context = @inflate_init($zlibEncoding);
            if ($context === false) {
                throw new UnexpectedValueException(sprintf('Failed to initialize decompression for encoding "%s"', $encoding));
            }

            $output = '';
            $outputLength = 0;
            $n = strlen($value);
            for ($offset = 0; $offset < $n; $offset += self::INFLATE_CHUNK_SIZE) {
                $chunk = @inflate_add(
                    $context,
                    substr($value, $offset, self::INFLATE_CHUNK_SIZE),
                    $offset + self::INFLATE_CHUNK_SIZE >= $n ? ZLIB_FINISH : ZLIB_SYNC_FLUSH,
                );
                if ($chunk === false) {
                    throw new UnexpectedValueException(sprintf('Failed to decode response body with encoding "%s"', $encoding));
                }
                $outputLength += strlen($chunk);
                if ($outputLength > $maxSize) {
                    throw new ResponseBodyTooLargeException($maxSize);
                }
                $output .= $chunk;
            }
            if (inflate_get_status($context) !== ZLIB_STREAM_END) {
                throw new UnexpectedValueException(sprintf('Truncated response body with encoding "%s"', $encoding));
            }

            return $output;
        }

        if (strtolower($encoding) === TransportFactoryInterface::COMPRESSION_BROTLI && function_exists('brotli_uncompress')) {
            // ext-brotli can't cap its output ($max_decoded_size is not enforced, and the streaming API has no
            // limit per call), so unlike gzip and deflate the memory is used before the size can be checked.
            // Only the size of the received body is bounded up front.
            $output = @brotli_uncompress($value);
            if ($output === false) {
                throw new UnexpectedValueException('Failed to decode response body with encoding "br"');
            }
            if (strlen($output) > $maxSize) {
                throw new ResponseBodyTooLargeException($maxSize);
            }

            return $output;
        }

        throw new UnexpectedValueException(sprintf('Not supported decompression encoding "%s"', $encoding));
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
