<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Trace\SpanExporter\NoopSpanExporter;
use RuntimeException;

class ExporterFactory
{
    /**
     * @throws RuntimeException
     */
    public function create(): SpanExporterInterface
    {
        $exporter = Configuration::getEnum(Variables::OTEL_TRACES_EXPORTER, 'none');
        if ($exporter === 'none') {
            return new NoopSpanExporter();
        }
        $factory = Loader::spanExporterFactory($exporter);

        return $factory->create();
    }
}
