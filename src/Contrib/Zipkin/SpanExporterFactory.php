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
        $timeout = Configuration::getInt(Variables::OTEL_EXPORTER_ZIPKIN_TIMEOUT)/1000;
        $transport = (new PsrTransportFactory())->create(endpoint: $endpoint, contentType: 'application/json', timeout: $timeout);

        return new Exporter($transport);
    }

    public function type(): string
    {
        return 'zipkin';
    }
    public function priority(): int
    {
        return 0;
    }
}
