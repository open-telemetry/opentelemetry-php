<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;

class LoggerProviderFactory
{
    public function create(?MeterProviderInterface $meterProvider = null, ?ResourceInfo $resource = null): LoggerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return NoopLoggerProvider::getInstance();
        }
        $exporter = (new ExporterFactory())->create();
        $processor = (new LogRecordProcessorFactory())->create($exporter, $meterProvider);
        $instrumentationScopeFactory = new InstrumentationScopeFactory((new LogRecordLimitsBuilder())->build()->getAttributeFactory());

        return new LoggerProvider($processor, $instrumentationScopeFactory, $resource);
    }
}
