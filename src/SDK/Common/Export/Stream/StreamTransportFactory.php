<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Stream;

use ErrorException;
use function fopen;
use function implode;
use function is_resource;
use LogicException;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function stream_context_create;

/**
 * @psalm-internal \OpenTelemetry
 */
final class StreamTransportFactory implements TransportFactoryInterface
{
    /**
     * @param string|resource $endpoint
     * @param array<string, string|string[]> $headers
     * @param string|string[]|null $compression
     *
     * @psalm-template CONTENT_TYPE of string
     * @psalm-param CONTENT_TYPE $contentType
     * @psalm-return TransportInterface<CONTENT_TYPE>
     * @throws ErrorException
     */
    public function create(
        $endpoint,
        string $contentType,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null,
    ): TransportInterface {
        assert(!empty($endpoint));
        $stream = is_resource($endpoint)
            ? $endpoint
            : self::createStream(
                $endpoint,
                $contentType,
                $headers,
                $timeout,
                $cacert,
                $cert,
                $key,
            );

        return new StreamTransport($stream, $contentType);
    }

    /**
     * @throws ErrorException
     * @return resource
     */
    private static function createStream(
        string $endpoint,
        string $contentType,
        array $headers = [],
        float $timeout = 10.,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null,
    ) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => self::createHeaderArray($contentType, $headers),
                'timeout' => $timeout,
            ],
            'ssl' => [
                'cafile' => $cacert,
                'local_cert' => $cert,
                'local_pk' => $key,
            ],
        ]);

        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        /**
         * @psalm-suppress PossiblyNullArgument
         */
        try {
            $stream = fopen($endpoint, 'ab', false, $context);
        } finally {
            restore_error_handler();
        }

        /** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
        if (!$stream) {
            throw new LogicException(sprintf('Failed opening stream "%s"', $endpoint));
        }

        return $stream;
    }

    private static function createHeaderArray(string $contentType, array $headers): array
    {
        $header = [];
        $header[] = sprintf('Content-Type: %s', $contentType);
        foreach ($headers as $name => $value) {
            $header[] = sprintf('%s: %s', $name, implode(', ', (array) $value));
        }

        return $header;
    }
}
