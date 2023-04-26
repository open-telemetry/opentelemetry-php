<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use InvalidArgumentException;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor
 */
class BatchLogsProcessorTest extends MockeryTestCase
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
        $logs = [];
        $timeout = 3000;

        for ($i = 0; $i < $batchSize; $i++) {
            $logs[] = $this->createMock(ReadWriteLogRecord::class);
        }

        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        $processor = new BatchLogsProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        foreach ($logs as $record) {
            $processor->onEmit($record);
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
        $logs = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $logs[] = $this->createMock(ReadWriteLogRecord::class);
        }

        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->expects($this->exactly($expectedFlush ? 1 : 0))->method('export');

        $processor = new BatchLogsProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize + 1
        );

        foreach ($logs as $i => $record) {
            if (1 === $i) {
                $this->testClock->advance($advanceByNano);
            }
            $processor->onEmit($record);
        }
    }

    public function scheduledDelayProvider(): array
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

        $logs = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            $logs[] = $this->createMock(ReadWriteLogRecord::class);
        }

        $exporter = Mockery::mock(LogRecordExporterInterface::class);
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $records) {
                        $this->assertCount(3, $records);
                        $this->assertInstanceOf(ReadWriteLogRecord::class, $records[0]);

                        return true;
                    }
                )
            )
            ->andReturn(new CompletedFuture(0));

        $processor = new BatchLogsProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        foreach ($logs as $idx => $record) {
            $processor->onEmit($record);

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

        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->expects($this->never())->method('export');

        $processor = new BatchLogsProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $record = $this->createMock(ReadWriteLogRecord::class);
            $processor->onEmit($record);
        }
    }

    public function test_export_includes_force_flush_on_shutdown(): void
    {
        $batchSize = 3;

        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->expects($this->once())->method('export');
        $exporter->expects($this->once())->method('shutdown');

        $proc = new BatchLogsProcessor($exporter, $this->createMock(ClockInterface::class));

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $record = $this->createMock(ReadWriteLogRecord::class);
            $proc->onEmit($record);
        }

        $proc->shutdown();
    }

    public function test_export_after_shutdown(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->expects($this->atLeastOnce())->method('shutdown');

        $proc = new BatchLogsProcessor($exporter, $this->createMock(ClockInterface::class));
        $proc->shutdown();

        $record = $this->createMock(ReadWriteLogRecord::class);
        $proc->onEmit($record, Context::getCurrent());
        $proc->forceFlush();
        $proc->shutdown();
    }

    public function test_force_flush(): void
    {
        $batchSize = 3;
        $queueSize = 3;
        $exportDelay = 2;
        $timeout = 3000;

        $exporter = Mockery::mock(LogRecordExporterInterface::class);
        $exporter->expects('forceFlush');
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $records) {
                        $this->assertCount(2, $records);
                        $this->assertInstanceOf(ReadWriteLogRecord::class, $records[0]);

                        return true;
                    }
                )
            )
            ->andReturn(new CompletedFuture(0));

        $processor = new BatchLogsProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $record = $this->createMock(ReadWriteLogRecord::class);
            $processor->onEmit($record);
        }

        $processor->forceFlush();
    }

    public function test_queue_size_exceeded_drops_spans(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $processor = new BatchLogsProcessor($exporter, $this->testClock, 5, 5000, 30000, 5);

        $exporter->expects($this->exactly(2))->method('export')->willReturnCallback(function (iterable $batch) use ($processor, &$i) {
            if ($i) {
                $this->assertCount(3, $batch);
            } else {
                for ($i = 0; $i < 5; $i++) {
                    $span = $this->createMock(ReadWriteLogRecord::class);
                    $processor->onEmit($span);
                }
            }

            return 0;
        });

        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);
        $processor->onEmit($record);

        $processor->forceFlush();
        $processor->forceFlush();
    }

    public function test_force_flush_applies_only_to_current_logs(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $processor = new BatchLogsProcessor($exporter, $this->testClock);

        $exporter->expects($this->exactly(1))->method('export')->willReturnCallback(function (iterable $batch) use ($processor) {
            $this->assertCount(1, $batch);

            $record = $this->createMock(ReadWriteLogRecord::class);
            $processor->onEmit($record); //arrives after flush started, so not flushed yet

            return 0;
        });

        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);

        $processor->forceFlush();
    }

    public function test_shutdown_shutdowns_exporter(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $processor = new BatchLogsProcessor($exporter, $this->testClock);

        $exporter->expects($this->once())->method('shutdown');
        $processor->shutdown();
    }

    public function test_throwing_exporter_export(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->method('forceFlush')->willReturn(true);
        $exporter->method('export')->willThrowException(new LogicException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $processor = new BatchLogsProcessor($exporter, $this->testClock);

        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);

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
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->method('forceFlush')->willThrowException(new LogicException());

        $this->expectException(LogicException::class);

        $processor = new BatchLogsProcessor($exporter, $this->testClock);
        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);

        $processor->forceFlush();
    }

    public function test_throwing_exporter_flush_cannot_rethrow_in_original_caller_logs_error(): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->method('forceFlush')->willReturnCallback(function () use (&$processor) {
            /** @var LogRecordProcessorInterface $processor */
            $record = $this->createMock(ReadWriteLogRecord::class);
            $processor->onEmit($record);

            return $processor->shutdown();
        });
        $exporter->method('shutdown')->willThrowException(new LogicException());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(LogLevel::ERROR);

        $processor = new BatchLogsProcessor($exporter, $this->testClock);

        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);

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
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $exporter->method('forceFlush')->willReturnCallback(function () use (&$processor) {
            /** @var LogRecordProcessorInterface $processor */
            $record = $this->createMock(ReadWriteLogRecord::class);
            $processor->onEmit($record);
            $processor->shutdown();

            throw new LogicException();
        });
        $exporter->expects($this->once())->method('shutdown');

        $this->expectException(LogicException::class);

        $processor = new BatchLogsProcessor($exporter, $this->testClock);

        $record = $this->createMock(ReadWriteLogRecord::class);
        $processor->onEmit($record);

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

        $exporter = $this->createMock(LogRecordExporterInterface::class);

        $processor = new BatchLogsProcessor(
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
                'otel.logs.log_processor.logs',
                'otel.logs.log_processor.queue.limit',
                'otel.logs.log_processor.queue.usage',
            ],
            array_column($metrics->collect(), 'name'),
        );
    }

    public function test_logs_processor_throws_on_invalid_max_queue_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        new BatchLogsProcessor($exporter, $this->testClock, -1);
    }

    public function test_logs_processor_throws_on_invalid_scheduled_delay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        new BatchLogsProcessor($exporter, $this->testClock, 2048, -1);
    }

    public function test_logs_processor_throws_on_invalid_export_timeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        new BatchLogsProcessor($exporter, $this->testClock, 2048, 5000, -1);
    }

    public function test_logs_processor_throws_on_invalid_max_export_batch_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        new BatchLogsProcessor($exporter, $this->testClock, 2048, 5000, 30000, -1);
    }

    public function test_logs_processor_throws_on_invalid_max_export_batch_size_exceeding_max_queue_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        new BatchLogsProcessor($exporter, $this->testClock, 2, 5000, 30000, 3);
    }
}
