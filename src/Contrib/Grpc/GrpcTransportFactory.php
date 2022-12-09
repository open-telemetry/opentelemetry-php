<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use function file_get_contents;
use Grpc\ChannelCredentials;
use function in_array;
use InvalidArgumentException;
use function json_encode;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function parse_url;
use RuntimeException;
use function sprintf;
use function substr_count;

final class GrpcTransportFactory implements TransportFactoryInterface
{
    use LogsMessagesTrait;

    /**
     * @psalm-param "application/x-protobuf" $contentType
     * @psalm-return TransportInterface<"application/x-protobuf">
     * @psalm-suppress MoreSpecificImplementedParamType
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function create(
        string $endpoint,
        string $contentType = 'application/x-protobuf',
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface {
        $parts = parse_url($endpoint);
        if (!isset($parts['scheme'], $parts['host'], $parts['path'])) {
            throw new InvalidArgumentException('Endpoint has to contain scheme, host and path');
        }
        /** @phpstan-ignore-next-line */
        if ($contentType !== 'application/x-protobuf') {
            throw new InvalidArgumentException(sprintf('Unsupported content type "%s", grpc transport supports only application/x-protobuf', $contentType));
        }

        $scheme = $parts['scheme'];
        $method = $parts['path'];

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException(sprintf('Endpoint contains not supported scheme "%s"', $scheme));
        }
        if (substr_count($parts['path'], '/') !== 2) {
            throw new InvalidArgumentException(sprintf('Endpoint path is not a valid GRPC method "%s"', $method));
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

        $grpcEndpoint = isset($parts['port'])
            ? $parts['host'] . ':' . $parts['port']
            : $parts['host'];

        return new GrpcTransport(
            $grpcEndpoint,
            $opts,
            $method,
            $headers,
        );
    }

    private static function createOpts(
        $compression,
        float $timeout,
        int $maxRetries,
        int $retryDelay
    ): array {
        $opts = [];

        // https://github.com/grpc/grpc/tree/master/src/php#compression
        static $algorithms = [
            TransportFactoryInterface::COMPRESSION_DEFLATE => 1,
            TransportFactoryInterface::COMPRESSION_GZIP => 2,
        ];
        $opts['grpc.default_compression_algorithm'] = 0;
        foreach ((array) $compression as $algorithm) {
            if (($flag = $algorithms[$algorithm] ?? null) !== null) {
                $opts['grpc.default_compression_algorithm'] = $flag;

                break;
            }
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
