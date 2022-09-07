<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Stream;

use ErrorException;
use function fopen;
use function implode;
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
    public function create(
        string $endpoint,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => self::createHeaderArray($headers),
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

        try {
            $stream = fopen($endpoint, 'ab', false, $context);
        } finally {
            restore_error_handler();
        }

        /** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
        if (!$stream) {
            throw new LogicException(sprintf('Failed opening stream "%s"', $endpoint));
        }

        /** @phan-suppress-next-line PhanPossiblyUndeclaredVariable */
        return new StreamTransport($stream);
    }

    private static function createHeaderArray(array $headers): array
    {
        $header = [];
        foreach ($headers as $name => $value) {
            $header[] = sprintf('%s: %s', $name, implode(', ', (array) $value));
        }

        return $header;
    }
}
