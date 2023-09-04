<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function array_filter;
use function array_map;
use function count;
use ErrorException;
use LogicException;
use function max;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use function rand;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function strcasecmp;
use function strtotime;
use Throwable;
use function time;
use function trim;
use UnexpectedValueException;

/**
 * @internal
 */
final class PsrUtils
{
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
            } catch (Throwable $e) {
                unset($encodings[$i]);
            }
        }

        $appliedEncodings = $encodings;

        return $value;
    }

    /**
     * @param list<string> $encodings
     */
    public static function decode(string $value, array $encodings): string
    {
        for ($i = count($encodings); --$i >= 0;) {
            if (strcasecmp($encodings[$i], 'identity') === 0) {
                continue;
            }
            if (!$decoder = self::decoder($encodings[$i])) {
                throw new UnexpectedValueException(sprintf('Not supported decompression encoding "%s"', $encodings[$i]));
            }

            $value = $decoder($value);
        }

        return $value;
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
        if (strpos($compression, ',') === false) {
            return [$compression];
        }

        return array_map('trim', explode(',', $compression));
    }

    private static function encoder(string $encoding): ?callable
    {
        static $encoders;

        /** @noinspection SpellCheckingInspection */
        $encoders ??= array_map(fn (callable $callable): callable => self::throwOnErrorOrFalse($callable), array_filter([
            TransportFactoryInterface::COMPRESSION_GZIP => 'gzencode',
            TransportFactoryInterface::COMPRESSION_DEFLATE => 'gzcompress',
            TransportFactoryInterface::COMPRESSION_BROTLI => 'brotli_compress',
        ], 'function_exists'));

        return $encoders[$encoding] ?? null;
    }

    private static function decoder(string $encoding): ?callable
    {
        static $decoders;

        /** @noinspection SpellCheckingInspection */
        $decoders ??= array_map(fn (callable $callable): callable => self::throwOnErrorOrFalse($callable), array_filter([
            TransportFactoryInterface::COMPRESSION_GZIP => 'gzdecode',
            TransportFactoryInterface::COMPRESSION_DEFLATE => 'gzuncompress',
            TransportFactoryInterface::COMPRESSION_BROTLI => 'brotli_uncompress',
        ], 'function_exists'));

        return $decoders[$encoding] ?? null;
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
