<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use function array_diff_key;
use function array_flip;
use function array_keys;
use function file_get_contents;
use Grpc\ChannelCredentials;
use function implode;
use InvalidArgumentException;
use function in_array;
use function json_encode;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function parse_url;
use RuntimeException;
use function sprintf;
use function strcasecmp;
use function substr_count;
use UnexpectedValueException;

final class GrpcTransportFactory implements TransportFactoryInterface
{
    public function create(
        string $endpoint,
        array $headers = [],
        ?string $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface {
        $parts = parse_url($endpoint);
        if ($unsupported = array_diff_key($parts, array_flip(['scheme', 'host', 'port', 'path']))) {
            throw new InvalidArgumentException(sprintf('Endpoint contains not supported parts %s', implode(', ', array_keys($unsupported))));
        }
        if (!isset($parts['path']) || substr_count($parts['path'], '/') !== 2) {
            throw new InvalidArgumentException(sprintf('Endpoint path is missing or invalid "%s"', $parts['path'] ?? ''));
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? null;
        $method = $parts['path'];

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException(sprintf('Endpoint contains not supported scheme "%s"', $scheme));
        }

        $opts = self::createOpts($compression, $timeout, $maxRetries, $retryDelay);
        /** @psalm-suppress PossiblyNullArgument */
        $opts['credentials'] = $scheme === 'http'
            ? ChannelCredentials::createInsecure()
            : ChannelCredentials::createSsl(
                self::fileGetContents($cacert),
                self::fileGetContents($key),
                self::fileGetContents($cert),
            );

        $grpcEndpoint = $port !== null
            ? $host . ':' . $port
            : $host;

        return new GrpcTransport(
            $grpcEndpoint,
            $opts,
            $method,
            $headers,
        );
    }

    private static function createOpts(
        ?string $compression,
        float $timeout,
        int $maxRetries,
        int $retryDelay
    ): array {
        $opts = [];

        // https://github.com/grpc/grpc/tree/master/src/php#compression
        if ($compression === null) {
            $opts['grpc.default_compression_algorithm'] = 0;
        } elseif (strcasecmp($compression, 'identity') === 0) {
            $opts['grpc.default_compression_algorithm'] = 0;
        } elseif (strcasecmp($compression, 'none') === 0) {
            $opts['grpc.default_compression_algorithm'] = 0;
        } elseif (strcasecmp($compression, 'deflate') === 0) {
            $opts['grpc.default_compression_algorithm'] = 1;
        } elseif (strcasecmp($compression, 'gzip') === 0) {
            $opts['grpc.default_compression_algorithm'] = 2;
        } else {
            throw new UnexpectedValueException(sprintf('Unsupported compression algorithm "%s"', $compression));
        }

        // https://github.com/grpc/grpc-proto/blob/master/grpc/service_config/service_config.proto
        $opts['grpc.service_config'] = json_encode([
            'methodConfig' => [
                [
                    'name' => [
                        [
                            'service' => null,
                            'method' => null,
                        ],
                    ],
                    'timeout' => sprintf('%0.6fs', $timeout),
                    'retryPolicy' => [
                        'maxAttempts' => $maxRetries,
                        'initialBackoff' => sprintf('%0.3fs', $retryDelay / 1000),
                        'maxBackoff' => sprintf('%0.3fs', ($retryDelay << $maxRetries - 1) / 1000),
                        'backoffMultiplier' => 2,
                        'retryableStatusCodes' => [
                            // https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md#otlpgrpc-response
                            'CANCELLED',
                            'DEADLINE_EXCEEDED',
                            'RESOURCE_EXHAUSTED',
                            'ABORTED',
                            'OUT_OF_RANGE',
                            'UNAVAILABLE',
                            'DATA_LOSS',
                        ],
                    ],
                ],
            ],
        ]);

        return $opts;
    }

    private static function fileGetContents(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        if ($file === '') {
            throw new RuntimeException();
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new RuntimeException();
        }

        return $content;
    }
}
