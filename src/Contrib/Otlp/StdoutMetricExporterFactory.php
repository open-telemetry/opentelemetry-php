<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\TemporalityPreference;

class StdoutMetricExporterFactory implements MetricExporterFactoryInterface
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/protocol/file-exporter.md#exporter-configuration
     */
    #[\Override]
    public function create(): MetricExporterInterface
    {
        $transport = (new StreamTransportFactory())->create('php://stdout', ContentTypes::NDJSON);
        $temporality = TemporalityPreference::fromName(
            Configuration::getEnum(Variables::OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE),
        );

        return new MetricExporter($transport, $temporality);
    }
}
