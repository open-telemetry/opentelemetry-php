<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use UnexpectedValueException;

class MetricExporterFactory
{
    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function create(): MetricExporterInterface
    {
        $protocol = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            ? Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL)
            : Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_PROTOCOL);

        return new MetricExporter($this->buildTransport($protocol));
    }

    private function buildTransport(string $protocol): TransportInterface
    {
        /**
         * @todo (https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk.md#periodic-exporting-metricreader)
         * - OTEL_METRIC_EXPORT_INTERVAL
         * - OTEL_METRIC_EXPORT_TIMEOUT
         */
        $endpoint = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)
            ? Configuration::getString(Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT)
            : Configuration::getString(Variables::OTEL_EXPORTER_OTLP_ENDPOINT);

        $headers = Configuration::has(Variables::OTEL_EXPORTER_OTLP_METRICS_HEADERS)
            ? Configuration::getMap(Variables::OTEL_EXPORTER_OTLP_METRICS_HEADERS)
            : Configuration::getMap(Variables::OTEL_EXPORTER_OTLP_HEADERS);
        $headers += OtlpUtil::getUserAgentHeader();

        switch ($protocol) {
            case KnownValues::VALUE_GRPC:
                return (new GrpcTransportFactory())->create(
                    $endpoint . OtlpUtil::method(Signals::METRICS),
                    ContentTypes::PROTOBUF,
                    $headers,
                );
            case KnownValues::VALUE_HTTP_PROTOBUF:
            case KnownValues::VALUE_HTTP_JSON:
                return PsrTransportFactory::discover()->create(
                    $endpoint,
                    Protocols::contentType($protocol),
                    $headers,
                );
            default:
                throw new UnexpectedValueException('Unknown otlp protocol: ' . $protocol);
        }
    }
}
