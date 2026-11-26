<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Behavior\Internal\ConfiguredLogger;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessorBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use RuntimeException;

#[CoversClass(TracerProvider::class)]
#[CoversClass(TracerProviderBuilder::class)]
#[CoversClass(TracerSharedState::class)]
#[CoversClass(Span::class)]
#[CoversClass(BatchSpanProcessor::class)]
#[CoversClass(BatchSpanProcessorBuilder::class)]
#[CoversClass(SimpleSpanProcessor::class)]
class TracerProviderLoggerTest extends TestCase
{
    use TestState;

    private LogWriterInterface&MockObject $globalWriter;

    #[\Override]
    public function setUp(): void
    {
        $this->globalWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->globalWriter);
    }

    public function test_span_logs_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            'Dropped span attributes, links or events',
            $this->callback(static fn (array $context): bool => $context['source'] === Span::class),
        );

        $tracerProvider = new TracerProvider(
            spanLimits: (new SpanLimitsBuilder())->setAttributeCountLimit(1)->build(),
            logger: $logger,
        );
        $tracerProvider->getTracer('test')->spanBuilder('span')
            ->setAttribute('a', 1)
            ->setAttribute('b', 2)
            ->startSpan()
            ->end();
    }

    public function test_builder_passes_logger_to_span(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            'Dropped span attributes, links or events',
            $this->callback(static fn (array $context): bool => $context['source'] === Span::class),
        );

        $this->setEnvironmentVariable('OTEL_SPAN_EVENT_COUNT_LIMIT', 1);

        $span = (new TracerProviderBuilder())->setLogger($logger)->build()->getTracer('test')->spanBuilder('span')->startSpan();
        $span->addEvent('a');
        $span->addEvent('b');
        $span->end();
    }

    public function test_batch_span_processor_logs_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('exceeds maximum queue size'),
            $this->callback(static fn (array $context): bool => $context['source'] === BatchSpanProcessor::class),
        );

        new BatchSpanProcessor(
            $this->createStub(SpanExporterInterface::class),
            Clock::getDefault(),
            maxQueueSize: 1,
            maxExportBatchSize: 2,
            logger: $logger,
        );
    }

    public function test_batch_span_processor_builder_passes_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::ERROR,
            'Unhandled export error',
            $this->callback(static fn (array $context): bool => $context['source'] === BatchSpanProcessor::class),
        );

        $processor = BatchSpanProcessor::builder($this->failingExporter())->setLogger($logger)->build();
        $this->endSpan($processor);
        $processor->forceFlush();
    }

    public function test_simple_span_processor_logs_to_injected_logger(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::ERROR,
            'Unhandled export error',
            $this->callback(static fn (array $context): bool => $context['source'] === SimpleSpanProcessor::class),
        );

        $this->endSpan(new SimpleSpanProcessor($this->failingExporter(), $logger));
    }

    public function test_batch_span_processor_subclass_can_declare_its_own_logging(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            $this->stringContains('exceeds maximum queue size'),
        );

        new class($this->createStub(SpanExporterInterface::class), Clock::getDefault(), maxQueueSize: 1, maxExportBatchSize: 2, logger: $logger) extends BatchSpanProcessor {
            use LogsMessagesTrait;

            protected function logger(): LoggerInterface
            {
                return new NullLogger();
            }
        };
    }

    public function test_simple_span_processor_subclass_can_declare_its_own_logging(): void
    {
        $this->globalWriter->expects($this->never())->method('write');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR, 'Unhandled export error');

        $this->endSpan(new class($this->failingExporter(), $logger) extends SimpleSpanProcessor {
            use LogsMessagesTrait;

            protected function logger(): LoggerInterface
            {
                return new NullLogger();
            }
        });
    }

    public function test_injected_level_applies_instead_of_global_level(): void
    {
        Logging::setLogLevel('error');
        $this->globalWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            'Dropped span attributes, links or events',
            $this->callback(static fn (array $context): bool => $context['source'] === Span::class),
        );

        $tracerProvider = new TracerProvider(
            spanLimits: (new SpanLimitsBuilder())->setAttributeCountLimit(1)->build(),
            logger: new ConfiguredLogger('warning'),
        );
        $tracerProvider->getTracer('test')->spanBuilder('span')
            ->setAttribute('a', 1)
            ->setAttribute('b', 2)
            ->startSpan()
            ->end();
    }

    public function test_without_logger_writes_to_global_writer(): void
    {
        $this->globalWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            'Dropped span attributes, links or events',
            $this->callback(static fn (array $context): bool => $context['source'] === Span::class),
        );

        $tracerProvider = new TracerProvider(spanLimits: (new SpanLimitsBuilder())->setAttributeCountLimit(1)->build());
        $tracerProvider->getTracer('test')->spanBuilder('span')
            ->setAttribute('a', 1)
            ->setAttribute('b', 2)
            ->startSpan()
            ->end();
    }

    public function test_without_logger_uses_global_level(): void
    {
        Logging::setLogLevel('error');
        $this->globalWriter->expects($this->never())->method('write');

        $tracerProvider = new TracerProvider(spanLimits: (new SpanLimitsBuilder())->setAttributeCountLimit(1)->build());
        $tracerProvider->getTracer('test')->spanBuilder('span')
            ->setAttribute('a', 1)
            ->setAttribute('b', 2)
            ->startSpan()
            ->end();
    }

    public function test_failing_logger_does_not_break_caller(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->willThrowException(new RuntimeException('logger failed'));

        $this->endSpan(new SimpleSpanProcessor($this->failingExporter(), $logger));
    }

    private function failingExporter(): SpanExporterInterface
    {
        $exporter = $this->createStub(SpanExporterInterface::class);
        $exporter->method('export')->willThrowException(new RuntimeException('export failed'));

        return $exporter;
    }

    private function endSpan(SpanProcessorInterface $processor): void
    {
        (new TracerProvider($processor))->getTracer('test')->spanBuilder('span')->startSpan()->end();
    }
}
