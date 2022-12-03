<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory implements SpanExporterFactoryInterface
{
    public function create(): SpanExporterInterface
    {
        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_ZIPKIN_ENDPOINT);
        $transport = PsrTransportFactory::discover()->create($endpoint, 'application/json');

        return new Exporter($transport);
    }
}
