<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;

class ExporterFactory
{
    public function create(): LogRecordExporterInterface
    {
        $name = Configuration::getEnum(Variables::OTEL_LOGS_EXPORTER, 'none');
        if ($name === 'none') {
            return new NoopExporter();
        }
        $factory = Loader::logRecordExporterFactory($name);

        return $factory->create();
    }
}
