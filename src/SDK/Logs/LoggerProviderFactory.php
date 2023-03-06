<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Sdk;

class LoggerProviderFactory
{
    public function create(?MeterProviderInterface $meterProvider = null): LoggerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return NoopLoggerProvider::getInstance();
        }
        $exporter = (new ExporterFactory())->create();
        $processor = (new LogRecordProcessorFactory())->create($exporter, $meterProvider);

        return new LoggerProvider($processor);
    }
}
