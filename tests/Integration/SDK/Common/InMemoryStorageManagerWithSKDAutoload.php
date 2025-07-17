<?php

declare(strict_types=1);

namespace Integration\SDK\Common;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\SdkAutoloader;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InMemoryStorageManagerWithSKDAutoload extends TestCase
{
    use TestState;

    protected LoggerInterface&MockObject $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
    }

    public function test_in_memory_storage_manager_for_metrics_with_sdk_autoload_enabled(): void
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

        $storage = InMemoryStorageManager::metrics();
        $this->assertEquals(1, $storage->count());
        $this->assertEquals('test_value', $storage[0]->name);
        $this->assertEquals(6, $storage[0]->data->dataPoints[0]->value);
    }

    public function test_in_memory_storage_manager_for_logs_with_sdk_autoload_enabled(): void
    {
        $this->setEnvironmentVariable('OTEL_LOGS_EXPORTER', 'memory');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();

        $loggerProvider = Globals::loggerProvider();
        $log = new LogRecord('some body');
        $loggerProvider->getLogger('test_logger')->emit($log);
        if ($loggerProvider instanceof LoggerProvider) {
            $loggerProvider->forceFlush();
        }

        $storage = InMemoryStorageManager::logs();
        $this->assertEquals(1, $storage->count());
        $this->assertEquals('some body', $storage[0]->getBody());
    }

    public function test_in_memory_storage_manager_for_traces_with_sdk_autoload_enabled(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', 'memory');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED, 'true');
        SdkAutoloader::autoload();

        $tracerProvider = Globals::tracerProvider();
        $tracer = $tracerProvider->getTracer('test_tracer');
        $tracer->spanBuilder('test_span_1')->startSpan()->end();
        $tracer->spanBuilder('test_span_2')->startSpan()->end();
        if ($tracerProvider instanceof TracerProvider) {
            $tracerProvider->forceFlush();
        }

        $storage = InMemoryStorageManager::spans();
        $this->assertEquals(2, $storage->count());
        $this->assertEquals('test_span_1', $storage[0]->getName());
        $this->assertEquals('test_span_2', $storage[1]->getName());
    }
}
