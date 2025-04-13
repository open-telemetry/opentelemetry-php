<?php

declare(strict_types=1);

namespace Integration\SDK\Common;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InMemoryStorageManagerWithSKDAutoload extends TestCase
{
    use TestState;

    protected LoggerInterface&MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
        Globals::reset();
    }

    public function test_in_memory_storage_manager_with_sdk_autoload_enabled(): void
    {
        $this->setEnvironmentVariable('OTEL_METRICS_EXPORTER', 'memory');
        $this->setEnvironmentVariable('OTEL_METRICS_EXEMPLAR_FILTER', 'all');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', 'cumulative');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();

        $meterProvider = Globals::meterProvider();

        $m = $meterProvider->getMeter('some_meter');
        $counter = $m->createCounter('test_value');
        $counter->add(1);
        $counter->add(5);

        if ($meterProvider instanceof MeterProvider) {
            $meterProvider->forceFlush();
        }

        $storage = InMemoryStorageManager::getStorageForMetrics();
        $this->assertEquals(1, $storage->count());
        $this->assertEquals('test_value', $storage[0]->name);
        $this->assertEquals(6, $storage[0]->data->dataPoints[0]->value);
    }
}
