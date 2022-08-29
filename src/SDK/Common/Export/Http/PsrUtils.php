<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function array_filter;
use function array_reverse;
use function explode;
use function max;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function rand;
use function sprintf;
use function strtolower;
use function strtotime;
use function time;
use function trim;
use UnexpectedValueException;

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

    public static function encodeStream(StreamInterface $stream, string $encodings, StreamFactoryInterface $streamFactory): StreamInterface
    {
        $value = $stream->__toString();
        foreach (explode(',', $encodings) as $encoding) {
            $encoding = strtolower(trim($encoding, " \t"));
            $value = self::encoder($encoding)($value);
        }

        return $streamFactory->createStream($value);
    }

    public static function decodeStream(StreamInterface $stream, string $encodings, StreamFactoryInterface $streamFactory): StreamInterface
    {
        $value = $stream->__toString();
        foreach (array_reverse(explode(',', $encodings)) as $encoding) {
            $encoding = strtolower(trim($encoding, " \t"));
            $value = self::decoder($encoding)($value);
        }

        return $streamFactory->createStream($value);
    }

    private static function encoder(string $encoding): callable
    {
        static $encoders;

        /** @noinspection SpellCheckingInspection */
        $encoders ??= array_filter([
            'gzip' => 'gzencode',
            'deflate' => 'gzcompress',
            'identity' => 'strval',
            'none' => 'strval',
            'br' => 'brotli_compress',
        ], 'function_exists');

        if (!$encoder = $encoders[$encoding] ?? null) {
            throw new UnexpectedValueException(sprintf('Not supported compression encoding "%s"', $encoding));
        }

        return $encoder;
    }

    private static function decoder(string $encoding): callable
    {
        static $decoders;

        /** @noinspection SpellCheckingInspection */
        $decoders ??= array_filter([
            'gzip' => 'gzdecode',
            'deflate' => 'gzuncompress',
            'identity' => 'strval',
            'none' => 'strval',
            'br' => 'brotli_uncompress',
        ], 'function_exists');

        if (!$decoder = $decoders[$encoding] ?? null) {
            throw new UnexpectedValueException(sprintf('Not supported decompression encoding "%s"', $encoding));
        }

        return $decoder;
    }
}
