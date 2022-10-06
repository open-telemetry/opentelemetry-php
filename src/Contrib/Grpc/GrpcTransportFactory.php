<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use function file_get_contents;
use Grpc\ChannelCredentials;
use function in_array;
use InvalidArgumentException;
use function json_encode;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function parse_url;
use RuntimeException;
use function sprintf;

final class GrpcTransportFactory implements TransportFactoryInterface
{
    use EnvironmentVariablesTrait;

    public const DEFAULT_ENDPOINT = 'http://localhost:4317';

    public function create(
        string $endpoint = null,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface {
        $endpoint ??= $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT) ?
            $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT) :
            $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, self::DEFAULT_ENDPOINT);

        if (!$cacert && ($this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE) || $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_CERTIFICATE))) {
            $cacert = $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE, Env::OTEL_EXPORTER_OTLP_CERTIFICATE);
        }

        $compression = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) :
            $this->getEnumFromEnvironment(
                Env::OTEL_EXPORTER_OTLP_COMPRESSION,
                $compression ? Values::VALUE_GZIP : Values::VALUE_NONE
            );

        $timeout = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_TIMEOUT) ?
            $this->getFloatFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_TIMEOUT, $timeout) :
            $this->getFloatFromEnvironment(Env::OTEL_EXPORTER_OTLP_TIMEOUT, $timeout);

        $headers += $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS, null) :
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS, null);
        $headers += OtlpUtil::getUserAgentHeader();

        $parts = parse_url($endpoint);
        if (!isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('Endpoint has to contain scheme and host');
        }

        $scheme = $parts['scheme'];

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

        $grpcEndpoint = isset($parts['port'])
            ? $parts['host'] . ':' . $parts['port']
            : $parts['host'];

        $client = new TraceServiceClient($grpcEndpoint, $opts);

        return new GrpcTransport(
            $client,
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
