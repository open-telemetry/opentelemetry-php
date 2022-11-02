<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\KnownValues;
use OpenTelemetry\SDK\Common\Environment\Variables;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class MetricExporterFactory
{
    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function create(): MetricExporterInterface
    {
        $protocol = EnvironmentVariables::has(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            ? EnvironmentVariables::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            : EnvironmentVariables::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);

        return new MetricExporter($this->buildTransport($protocol));
    }

    private function buildTransport(string $protocol): TransportInterface
    {
        $endpoint = EnvironmentVariables::has(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)
            ? EnvironmentVariables::getString(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)
            : EnvironmentVariables::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT);
        switch ($protocol) {
            case KnownValues::VALUE_GRPC:
                return (new GrpcTransportFactory())->create($endpoint . OtlpUtil::method(Signals::METRICS));
            case KnownValues::VALUE_HTTP_PROTOBUF:
            case KnownValues::VALUE_HTTP_JSON:
                return PsrTransportFactory::discover()->create($endpoint, Protocols::contentType($protocol));
            default:
                throw new \UnexpectedValueException('Unknown otlp protocol: ' . $protocol);
        }
    }
}
