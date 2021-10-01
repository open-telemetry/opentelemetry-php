<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace\SpanProcessor;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanData;
use OpenTelemetry\Sdk\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Sdk\Trace\Test\TestClock;
use OpenTelemetry\Trace as API;

class BatchSpanProcessorTest extends MockeryTestCase
{
    private TestClock $testClock;

    protected function setUp(): void
    {
        $this->testClock = new TestClock();

        Clock::setTestClock($this->testClock);
    }

    protected function tearDown(): void
    {
        Clock::setTestClock();
    }

    public function test_allowsNullExporter(): void
    {
        $proc = new BatchSpanProcessor(null, $this->testClock);
        /** @var Span $span */
        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
        $this->assertTrue(true); // phpunit requires an assertion
    }

    public function test_export_batchSizeMet(): void
    {
        $batchSize = 3;
        $queueSize = 5; // queue is larger than batch
        $exportDelay = 3;
        $spans = [];
        $timeout = 3000;

        for ($i = 0; $i < $batchSize; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        /** @var Span[] $spans */
        /** @var Exporter $exporter */
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

    public function test_export_delayLimitReached_partiallyFilledBatch(): void
    {
        $batchSize = 4;
        $queueSize = 5;
        $exportDelay = 1;
        $timeout = 3000;

        $spans = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = Mockery::mock(Exporter::class);
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $spans) {
                        $this->assertCount(3, $spans);
                        $this->assertInstanceOf(SpanData::class, $spans[0]);

                        return true;
                    }
                )
            );

        /** @var Exporter $exporter */
        /** @var Clock $clock */
        $processor = new BatchSpanProcessor(
            $exporter,
            $this->testClock,
            $queueSize,
            $exportDelay,
            $timeout,
            $batchSize
        );

        /** @var Span $span */
        foreach ($spans as $idx => $span) {
            $processor->onEnd($span);

            if (1 === $idx) {
                // Advance the clock to force a timeout flush.
                $this->testClock->advanceSeconds();
            }
        }
    }

    public function test_export_delayLimitNotReached_partiallyFilledBatch(): void
    {
        $batchSize = 3;
        $queueSize = 5;
        $exportDelay = 2;
        $timeout = 3000;

        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->never())->method('export');

        /** @var Exporter $exporter */
        /** @var Clock $clock */
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

    public function test_export_afterShutdown(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->once())->method('shutdown');

        $proc = new BatchSpanProcessor($exporter, $this->createMock(Clock::class));
        $proc->shutdown();

        /** @var Span $span */
        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
        $proc->onEnd($span);
    }

    public function test_export_onlySampledSpans(): void
    {
        $sampledSpan = $this->createSampledSpanMock();
        $nonSampledSpan = $this->createNonSampledSpanMock();

        $exporter = Mockery::mock(Exporter::class);
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

    public function test_forceFlush_endedSpans(): void
    {
        $batchSize = 3;
        $queueSize = 3;
        $exportDelay = 2;
        $timeout = 3000;

        $exporter = Mockery::mock(Exporter::class);
        $exporter
            ->expects('export')
            ->with(
                Mockery::on(
                    function (array $spans) {
                        $this->assertCount(2, $spans);
                        $this->assertInstanceOf(SpanData::class, $spans[0]);

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
            /** @var Span $span */
            $span = $this->createSampledSpanMock();
            $processor->onEnd($span);
        }

        $processor->forceFlush();
    }

    public function test_shutdown_shutdownsExporter(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $processor = new BatchSpanProcessor($exporter, $this->testClock);

        $exporter->expects($this->once())->method('shutdown');
        $processor->shutdown();
    }

    private function createSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(API\SpanContext::class, ['isSampled' => true]);

        return $this->createConfiguredMock(Span::class, ['getContext' => $spanContext]);
    }

    private function createNonSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(API\SpanContext::class, ['isSampled' => false]);

        return $this->createConfiguredMock(Span::class, ['getContext' => $spanContext]);
    }
}
