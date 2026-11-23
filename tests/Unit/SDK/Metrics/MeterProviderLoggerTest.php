<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Behavior\Internal\ConfiguredLogger;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\SDK\Metrics\Meter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

#[CoversClass(MeterProvider::class)]
#[CoversClass(MeterProviderBuilder::class)]
#[CoversClass(Meter::class)]
#[CoversClass(MetricRegistry::class)]
#[CoversClass(StreamFactory::class)]
class MeterProviderLoggerTest extends TestCase
{
    use TestState;

    private LogWriterInterface&MockObject $globalWriter;

    #[\Override]
    public function setUp(): void
    {
        $this->globalWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->globalWriter);
    }

    public function test_meter_logs_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('instrument not created by this SDK'),
            $this->callback(static fn (array $context): bool => $context['source'] === Meter::class),
        );

        $meter = (new MeterProviderBuilder())->setLogger($logger)->build()->getMeter('test');
        $meter->batchObserve(static fn () => null, $this->createStub(ObservableCounterInterface::class));
    }

    public function test_meter_logs_instrument_from_other_meter_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('instrument not created by this meter'),
            $this->callback(static fn (array $context): bool => $context['source'] === Meter::class),
        );

        $meterProvider = (new MeterProviderBuilder())->setLogger($logger)->build();
        $instrument = $meterProvider->getMeter('other')->createObservableCounter('counter');
        $meterProvider->getMeter('test')->batchObserve(static fn () => null, $instrument);
    }

    public function test_metric_registry_logs_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            'Asynchronous instrument callback should not return a value, it is ignored',
            $this->callback(static fn (array $context): bool => $context['source'] === MetricRegistry::class),
        );

        $reader = new ExportingReader(new InMemoryExporter());
        $meterProvider = (new MeterProviderBuilder())->addReader($reader)->setLogger($logger)->build();
        $meterProvider->getMeter('test')->createObservableCounter('counter', callbacks: static fn () => 1);
        $reader->collect();
    }

    /**
     * A provider built with its own level logs at that level, whatever the global level is.
     */
    public function test_injected_level_applies_instead_of_global_level(): void
    {
        Logging::setLogLevel('error');
        $this->globalWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            $this->stringContains('instrument not created by this SDK'),
            $this->callback(static fn (array $context): bool => $context['source'] === Meter::class),
        );

        $meter = (new MeterProviderBuilder())->setLogger(new ConfiguredLogger('warning'))->build()->getMeter('test');
        $meter->batchObserve(static fn () => null, $this->createStub(ObservableCounterInterface::class));
    }

    public function test_without_logger_uses_global_level(): void
    {
        Logging::setLogLevel('error');
        $this->globalWriter->expects($this->never())->method('write');

        $meter = (new MeterProviderBuilder())->build()->getMeter('test');
        $meter->batchObserve(static fn () => null, $this->createStub(ObservableCounterInterface::class));
    }

    public function test_without_logger_writes_to_global_writer(): void
    {
        $this->globalWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            $this->stringContains('instrument not created by this SDK'),
            $this->callback(static fn (array $context): bool => $context['source'] === Meter::class),
        );

        $meter = (new MeterProviderBuilder())->build()->getMeter('test');
        $meter->batchObserve(static fn () => null, $this->createStub(ObservableCounterInterface::class));
    }

    public function test_failing_logger_does_not_break_caller(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->willThrowException(new RuntimeException('logger failed'));

        $meter = (new MeterProviderBuilder())->setLogger($logger)->build()->getMeter('test');
        $meter->batchObserve(static fn () => null, $this->createStub(ObservableCounterInterface::class));
    }
}
