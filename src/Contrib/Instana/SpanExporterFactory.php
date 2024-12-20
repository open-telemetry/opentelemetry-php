<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instana;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class SpanExporterFactory implements SpanExporterFactoryInterface
{
    public function create(): SpanExporterInterface
    {
        $endpoint = Configuration::getString(Variables::OTEL_EXPORTER_INSTANA_ENDPOINT);
        $timeout = Configuration::getInt(Variables::OTEL_EXPORTER_INSTANA_TIMEOUT) / 1000;

        $transport = new InstanaTransport($endpoint, $timeout);

        $uuid = $transport->getUuid();
        $pid = $transport->getPid();
        $converter = new SpanConverter($uuid, $pid);

        return new SpanExporter($transport, $converter);
    }
}
