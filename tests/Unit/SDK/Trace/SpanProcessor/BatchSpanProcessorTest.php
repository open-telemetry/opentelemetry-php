<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use function array_column;
use InvalidArgumentException;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor
 */
class BatchSpanProcessorTest extends MockeryTestCase
{
    private TestClock $testClock;

    protected function setUp(): void
    {
        LoggerHolder::set(new NullLogger());
        $this->testClock = new TestClock();

        ClockFactory::setDefault($this->testClock);
    }

    protected function tearDown(): void
    {
        ClockFactory::setDefault(null);
    }

    public function test_export_batch_size_met(): void
    {
        $batchSize = 3;
        $queueSize = 5; // queue is larger than batch
        $exportDelay = 3;
        $spans = [];
        $timeout = 3000;

        for ($i = 0; $i < $batchSize; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        /** @var SpanExporterInterface $exporter */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        foreach ($spans as $span) {
            $processor->onEnd($span);
        }
    }

    /**
     * @dataProvider scheduledDelayProvider
     */
    public function test_export_scheduled_delay(int $exportDelay, int $advanceByNano, bool $expectedFlush): void
    {
        $batchSize = 2;
        $queueSize = 5;
        $timeout = 3000;
        $spans = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->expects($this->exactly($expectedFlush ? 1 : 0))->method('export');

        /** @var SpanExporterInterface $exporter */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize + 1
        );

        foreach ($spans as $i => $span) {
            if (1 === $i) {
                $this->testClock->advance($advanceByNano);
            }
            $processor->onEnd($span);
        }
    }

    public function scheduledDelayProvider()
    {
        return [
            'no clock advance' => [1000, 0, false],
            'clock advance less than threshold' => [1000, 999 * ClockInterface::NANOS_PER_MILLISECOND, false],
            'clock advance equals threshold' => [1000, 1000 * ClockInterface::NANOS_PER_MILLISECOND, false],
            'clock advance exceeds threshold' => [1000, 1001 * ClockInterface::NANOS_PER_MILLISECOND, true],
        ];
    }

    public function test_export_delay_limit_reached_partially_filled_batch(): void
    {
        $batchSize = 4;
        $queueSize = 5;
        $exportDelay = 1;
        $timeout = 3000;

        $spans = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = Mockery::mock(SpanExporterInterface::class);
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $spans) {
                        $this->assertCount(3, $spans);
                        $this->assertInstanceOf(SpanDataInterface::class, $spans[0]);

                        return true;
                    }
                )
            )
            ->andReturn(new CompletedFuture(0));

        /** @var SpanExporterInterface $exporter */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        foreach ($spans as $idx => $span) {
            $processor->onEnd($span);

            if (1 === $idx) {
                // Advance the clock to force a timeout flush.
                $this->testClock->advanceSeconds();
            }
        }
    }

    public function test_export_delay_limit_not_reached_partially_filled_batch(): void
    {
        $batchSize = 3;
        $queueSize = 5;
        $exportDelay = 2;
        $timeout = 3000;

        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->expects($this->never())->method('export');

        /** @var SpanExporterInterface $exporter */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $mock_span = $this->createSampledSpanMock();
            $processor->onEnd($mock_span);
        }
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk.md#shutdown-1
     */
    public function test_export_includes_force_flush_on_shutdown(): void
    {
        $batchSize = 3;

        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->expects($this->once())->method('export');
        $exporter->expects($this->once())->method('shutdown');

        $proc = new BatchSpanProcessor($exporter, $this->createMock(ClockInterface::class));

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $mock_span = $this->createSampledSpanMock();
            $proc->onEnd($mock_span);
        }

        $proc->shutdown();
    }

    public function test_export_after_shutdown(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->expects($this->atLeastOnce())->method('shutdown');

        $proc = new BatchSpanProcessor($exporter, $this->createMock(ClockInterface::class));
        $proc->shutdown();

        $span = $this->createSampledSpanMock();
        $proc->onStart($span, Context::getCurrent());
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
    }

    public function test_export_only_sampled_spans(): void
    {
        $sampledSpan = $this->createSampledSpanMock();
        $nonSampledSpan = $this->createNonSampledSpanMock();

        $exporter = Mockery::mock(SpanExporterInterface::class);
        $exporter->expects('forceFlush');
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $spans) use ($sampledSpan) {
                        $this->assertCount(1, $spans);
                        $this->assertEquals($sampledSpan->toSpanData(), $spans[0]);

                        return true;
                    }
                )
            )
            ->andReturn(new CompletedFuture(0));

        $batchProcessor = new BatchSpanProcessor($exporter, $this->testClock);
        foreach ([$sampledSpan, $nonSampledSpan] as $span) {
            $batchProcessor->onEnd($span);
        }

        $batchProcessor->forceFlush();
    }

    public function test_force_flush_ended_spans(): void
    {
        $batchSize = 3;
        $queueSize = 3;
        $exportDelay = 2;
        $timeout = 3000;

        $exporter = Mockery::mock(SpanExporterInterface::class);
        $exporter->expects('forceFlush');
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $spans) {
                        $this->assertCount(2, $spans);
                        $this->assertInstanceOf(SpanDataInterface::class, $spans[0]);

                        return true;
                    }
                )
            )
            ->andReturn(new CompletedFuture(0));

        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $span = $this->createSampledSpanMock();
            $processor->onEnd($span);
        }

        $processor->forceFlush();
    }

    public function test_queue_size_exceeded_drops_spans(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $processor = new BatchSpanProcessor($exporter, $this->testClock, 5, 5000, 30000, 5);

        $exporter->expects($this->exactly(2))->method('export')->willReturnCallback(function (iterable $batch) use ($processor, &$i) {
            if ($i) {
                $this->assertCount(3, $batch);
            } else {
                for ($i = 0; $i < 5; $i++) {
                    $span = $this->createSampledSpanMock();
                    $processor->onStart($span, Context::getCurrent());
                    $processor->onEnd($span);
                }
            }

            return 0;
        });

        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $processor->forceFlush();
        $processor->forceFlush();
    }

    public function test_force_flush_applies_only_to_current_spans(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $exporter->expects($this->exactly(1))->method('export')->willReturnCallback(function (iterable $batch) use ($processor) {
            $this->assertCount(1, $batch);

            $span = $this->createSampledSpanMock();
            $processor->onStart($span, Context::getCurrent());
            $processor->onEnd($span);

            return 0;
        });

        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $processor->forceFlush();
    }

    public function test_shutdown_shutdowns_exporter(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $exporter->expects($this->once())->method('shutdown');
        $processor->shutdown();
    }

    public function test_throwing_exporter_export(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willReturn(true);
        $exporter->method('export')->willThrowException(new LogicException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $previousLogger = LoggerHolder::get();
        LoggerHolder::set($logger);

        try {
            $processor->forceFlush();
        } finally {
            LoggerHolder::set($previousLogger);
        }
    }

    public function test_throwing_exporter_flush(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willThrowException(new LogicException());

        $this->expectException(LogicException::class);

        $processor = new BatchSpanProcessor($exporter, $this->testClock);
        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $processor->forceFlush();
    }

    public function test_throwing_exporter_flush_cannot_rethrow_in_original_caller_logs_error(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willReturnCallback(function () use (&$processor) {
            /** @var SpanProcessorInterface $processor */
            $span = $this->createSampledSpanMock();
            $processor->onStart($span, Context::getCurrent());
            $processor->onEnd($span);

            return $processor->shutdown();
        });
        $exporter->method('shutdown')->willThrowException(new LogicException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $previousLogger = LoggerHolder::get();
        LoggerHolder::set($logger);

        try {
            $processor->forceFlush();
        } finally {
            LoggerHolder::set($previousLogger);
        }
    }

    public function test_throwing_exporter_flush_rethrows_in_original_caller(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willReturnCallback(function () use (&$processor) {
            /** @var SpanProcessorInterface $processor */
            $span = $this->createSampledSpanMock();
            $processor->onStart($span, Context::getCurrent());
            $processor->onEnd($span);
            $processor->shutdown();

            throw new LogicException();
        });
        $exporter->expects($this->once())->method('shutdown');

        $this->expectException(LogicException::class);

        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $span = $this->createSampledSpanMock();
        $processor->onStart($span, Context::getCurrent());
        $processor->onEnd($span);

        $processor->forceFlush();
    }

    /**
     * @requires PHP >= 8.0
     */
    public function test_self_diagnostics(): void
    {
        $clock = new TestClock();
        $metrics = new InMemoryExporter();
        $reader = new ExportingReader($metrics);
        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            $clock,
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$reader],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );

        $exporter = $this->createMock(SpanExporterInterface::class);

        $processor = new BatchSpanProcessor(
            $exporter,
            ClockFactory::getDefault(),
            2048,
            5000,
            30000,
            512,
            false,
            $meterProvider,
        );

        $reader->collect();
        $this->assertEquals(
            [
                'otel.trace.span_processor.spans',
                'otel.trace.span_processor.queue.limit',
                'otel.trace.span_processor.queue.usage',
            ],
            array_column($metrics->collect(), 'name'),
        );
    }

    public function test_span_processor_throws_on_invalid_max_queue_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(SpanExporterInterface::class);
        new BatchSpanProcessor($exporter, $this->testClock, -1);
    }

    public function test_span_processor_throws_on_invalid_scheduled_delay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(SpanExporterInterface::class);
        new BatchSpanProcessor($exporter, $this->testClock, 2048, -1);
    }

    public function test_span_processor_throws_on_invalid_export_timeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(SpanExporterInterface::class);
        new BatchSpanProcessor($exporter, $this->testClock, 2048, 5000, -1);
    }

    public function test_span_processor_throws_on_invalid_max_export_batch_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(SpanExporterInterface::class);
        new BatchSpanProcessor($exporter, $this->testClock, 2048, 5000, 30000, -1);
    }

    public function test_span_processor_throws_on_invalid_max_export_batch_size_exceeding_max_queue_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(SpanExporterInterface::class);
        new BatchSpanProcessor($exporter, $this->testClock, 2, 5000, 30000, 3);
    }

    private function createSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(API\SpanContextInterface::class, ['isSampled' => true]);

        return $this->createConfiguredMock(ReadWriteSpanInterface::class, ['getContext' => $spanContext]);
    }

    private function createNonSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(API\SpanContextInterface::class, ['isSampled' => false]);

        return $this->createConfiguredMock(ReadWriteSpanInterface::class, ['getContext' => $spanContext]);
    }
}
