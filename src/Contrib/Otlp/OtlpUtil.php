<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function explode;
use OpenTelemetry\API\Signals;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\Detectors\Sdk;
use OpenTelemetry\SemConv\ResourceAttributes;
use UnexpectedValueException;

class OtlpUtil
{
    /**
     * gRPC per-signal methods
     * @see protobuf *ServiceClient
     */
    private const METHODS = [
        Signals::TRACE => '/opentelemetry.proto.collector.trace.v1.TraceService/Export',
        Signals::METRICS => '/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
        Signals::LOGS => '/opentelemetry.proto.collector.logs.v1.LogsService/Export',
    ];
    private const HEADER_VARS = [
        Signals::TRACE => Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS,
        Signals::METRICS => Variables::OTEL_EXPORTER_OTLP_METRICS_HEADERS,
        Signals::LOGS => Variables::OTEL_EXPORTER_OTLP_LOGS_HEADERS,
    ];

    public static function method(string $signal): string
    {
        if (!array_key_exists($signal, self::METHODS)) {
            throw new UnexpectedValueException('gRPC method not defined for signal: ' . $signal);
        }

        return self::METHODS[$signal];
    }

    /**
     * @param 'trace'|'metrics'|'logs' $signal
     * @param 'grpc'|'http/protobuf'|'http/json' $protocol
     */
    public static function path(string $signal, string $protocol): string
    {
        return match (explode('/', $protocol)[0]) { // @phpstan-ignore-line
            'grpc' => self::method($signal),
            'http' => match ($signal) {
                Signals::TRACE => '/v1/traces',
                Signals::METRICS => '/v1/metrics',
                Signals::LOGS => '/v1/logs',
            },
        };
    }

    public static function getHeaders(string $signal): array
    {
        $headers = Configuration::has(self::HEADER_VARS[$signal]) ?
            Configuration::getMap(self::HEADER_VARS[$signal]) :
            Configuration::getMap(Variables::OTEL_EXPORTER_OTLP_HEADERS);
        $headers += self::getUserAgentHeader();

        return array_map('rawurldecode', $headers);
    }

    /**
     * @link https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#user-agent
     */
    public static function getUserAgentHeader(): array
    {
        static $header;
        if ($header === null) {
            $resource = (new Sdk())->getResource();

            $header = ['User-Agent' => sprintf(
                'OTel OTLP Exporter PHP/%s',
                $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION) ?: 'unknown'
            )];
        }

        return $header;
    }
}
