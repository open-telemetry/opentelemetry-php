<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Time\AbstractClock;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use ReflectionObject;

/**
 * @covers OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor
 */
class BatchSpanProcessorTest extends MockeryTestCase
{
    use EnvironmentVariables;

    private TestClock $testClock;

    protected function setUp(): void
    {
        $this->testClock = new TestClock();

        ClockFactory::setDefault($this->testClock);
    }

    protected function tearDown(): void
    {
        ClockFactory::setDefault(null);
        $this->restoreEnvironmentVariables();
    }

    public function test_allows_null_exporter(): void
    {
        $proc = new BatchSpanProcessor(null, $this->testClock);
        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
        $this->assertTrue(true); // phpunit requires an assertion
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

    public function test_export_batch_size_greater_than_queue_size_is_rejected(): void
    {
        $batchSize = 3;
        $queueSize = 2; // queue is smaller than batch
        $exportDelay = 3;
        $timeout = 3000;

        $exporter = $this->createMock(SpanExporterInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        /** @var SpanExporterInterface $exporter */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );
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
        $exporter->expects($this->exactly($expectedFlush ? 1 : 0))->method('forceFlush');

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
            'clock advance less than threshold' => [1000, 999 * AbstractClock::NANOS_PER_MILLISECOND, false],
            'clock advance equals threshold' => [1000, 1000 * AbstractClock::NANOS_PER_MILLISECOND, false],
            'clock advance exceeds threshold' => [1000, 1001 * AbstractClock::NANOS_PER_MILLISECOND, true],
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
        $exporter->expects('forceFlush');
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
            );

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

        $proc = new BatchSpanProcessor($exporter, $this->createMock(AbstractClock::class));

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

        $proc = new BatchSpanProcessor($exporter, $this->createMock(AbstractClock::class));
        $proc->shutdown();

        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
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
            );

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
            );

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

    public function test_shutdown_shutdowns_exporter(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $exporter->expects($this->once())->method('shutdown');
        $processor->shutdown();
    }

    public function test_create_from_environment_variables(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);

        $input = [
            ['OTEL_BSP_MAX_EXPORT_BATCH_SIZE', 'maxExportBatchSize', 1],
            ['OTEL_BSP_MAX_QUEUE_SIZE', 'maxQueueSize', 2],
            ['OTEL_BSP_SCHEDULE_DELAY', 'scheduledDelayMillis', 3],
            ['OTEL_BSP_EXPORT_TIMEOUT', 'exporterTimeoutMillis', 4],
        ];
        foreach ($input as $i) {
            $this->setEnvironmentVariable($i[0], $i[2]);
        }
        $processor = new BatchSpanProcessor($exporter);
        $reflection = new ReflectionObject($processor);
        foreach ($input as $i) {
            $attr = $reflection->getProperty($i[1]);
            $attr->setAccessible(true);
            $this->assertEquals($i[2], $attr->getValue($processor));
        }
    }

    public function test_create_non_numeric_environment_value_throws_exception(): void
    {
        $this->setEnvironmentVariable('OTEL_BSP_MAX_QUEUE_SIZE', 'fruit');
        $exporter = $this->createMock(SpanExporterInterface::class);
        $this->expectException(Exception::class);
        new BatchSpanProcessor($exporter);
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
