<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * @deprecated The Zipkin exporter is deprecated. Use OTLP instead.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk_exporters/zipkin.md
 */
class SpanExporterFactory implements SpanExporterFactoryInterface
{
    /**
     * @phan-suppress PhanDeprecatedClass
     */
    #[\Override]
    public function create(): SpanExporterInterface
    {
        trigger_error('The Zipkin exporter is deprecated and will be removed in a future release. Use the OTLP exporter instead. See https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk_exporters/zipkin.md', E_USER_DEPRECATED);

        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_ZIPKIN_ENDPOINT);
        $timeout = Configuration::getInt(Variables::OTEL_EXPORTER_ZIPKIN_TIMEOUT)/1000;
        $transport = (new PsrTransportFactory())->create(endpoint: $endpoint, contentType: 'application/json', timeout: $timeout);

        return new Exporter($transport);
    }
}
