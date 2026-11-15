<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;
use OpenTelemetry\SDK\Registry;

class ExporterFactory
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#enum
     */
    public function create(): LogRecordExporterInterface
    {
        $exporters = array_map('strtolower', Configuration::getList(Variables::OTEL_LOGS_EXPORTER));
        if (1 !== count($exporters)) {
            throw new InvalidArgumentException(sprintf('Configuration %s requires exactly 1 exporter', Variables::OTEL_LOGS_EXPORTER));
        }
        $exporter = $exporters[0];
        if ($exporter === 'none') {
            return new NoopExporter();
        }
        $factory = Registry::logRecordExporterFactory($exporter);

        return $factory->create();
    }
}
