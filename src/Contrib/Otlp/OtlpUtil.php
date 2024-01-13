<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
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

    public static function method(string $signal): string
    {
        if (!array_key_exists($signal, self::METHODS)) {
            throw new UnexpectedValueException('gRPC method not defined for signal: ' . $signal);
        }

        return self::METHODS[$signal];
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
