<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\Registry;

/**
 * @internal
 */
final class Registrar implements RegistrarInterface
{
    #[\Override]
    public function register(): void
    {
        Registry::registerSpanExporterFactory('otlp', SpanExporterFactory::class);
        Registry::registerSpanExporterFactory('otlp/stdout', StdoutSpanExporterFactory::class);

        Registry::registerMetricExporterFactory('otlp', MetricExporterFactory::class);
        Registry::registerMetricExporterFactory('otlp/stdout', StdoutMetricExporterFactory::class);

        Registry::registerTransportFactory('http', OtlpHttpTransportFactory::class);

        Registry::registerLogRecordExporterFactory('otlp', LogsExporterFactory::class);
        Registry::registerLogRecordExporterFactory('otlp/stdout', StdoutLogsExporterFactory::class);
    }
}
