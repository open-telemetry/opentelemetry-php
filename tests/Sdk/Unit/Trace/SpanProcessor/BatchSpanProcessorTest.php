<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace\SpanProcessor;

use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanProcessor\BatchSpanProcessor;
use PHPUnit\Framework\TestCase;

class BatchSpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExportIfBatchLimitIsReachedButDelayNotReached()
    {
        $batchSize = 3;
        $queueSize = 5; // queue is larger than batch
        $exportDelay = 3;
        $spans = [];
        $timeout = 3000;

        for ($i = 0; $i < $batchSize; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        // Export will still happen even if clock will never trigger the batch
        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay - 1)));

        /** @var \OpenTelemetry\Sdk\Trace\Span[] $spans */
        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        foreach ($spans as $span) {
            $processor->onEnd($span);
        }
    }

    /**
     * @test
     */
    public function shouldExportIfDelayLimitReachedButBatchSizeNotReached()
    {
        $batchSize = 4;
        $queueSize = 5;
        $exportDelay = 1;
        $timeout = 3000;

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(1))->method('export')->with($spans);

        // The clock will be "before" the delay until the final call, then the timeout will trigger
        $clock = self::createMock(Clock::class);

        $timestampReturns = [];
        for ($i = 0; $i < count($spans) - 1; $i++) {
            $timestampReturns[] = ($exportDelay - 1) * 1e6;
        }
        $timestampReturns[count($spans) - 1] = ($exportDelay + 1) * 1e6;

        // forceFlush method will call timestamp once again to set exportedTimestamp
        $timestampReturns[count($spans)] = ($exportDelay - 1) * 1e6;

        $clock
            ->method('timestamp')
            ->willReturnOnConsecutiveCalls(...array_map(function ($e) {
                return (int) $e;
            }, $timestampReturns));

        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        foreach ($spans as $span) {
            /** @var \OpenTelemetry\Sdk\Trace\Span $mock_span */
            $processor->onEnd($span);
        }
    }

    /**
     * @test
     */
    public function shouldNotExportIfNotEnoughTimePassedAndBatchNotFull()
    {
        $batchSize = 3;
        $queueSize = 5;
        $exportDelay = 2;
        $timeout = 3000;

        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay - 1)));

        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(0))->method('export');

        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $mock_span = $this->createSampledSpanMock();
            /** @var \OpenTelemetry\Sdk\Trace\Span $mock_span */
            $processor->onEnd($mock_span);
        }
    }

    /**
     * @test
     */
    public function shouldAllowNullExporter()
    {
        $proc = new BatchSpanProcessor(null, self::createMock(Clock::class));
        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
        $proc->onEnd($span);
        $proc->forceFlush();
        $proc->shutdown();
        $this->assertTrue(true); // phpunit requires an assertion
    }

    /**
     * @test
     */
    public function forceFlushExportsAllEndedSpans()
    {
        $batchSize = 3;
        $queueSize = 3;
        $exportDelay = 2;
        $timeout = 3000;

        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay - 1)));

        $exporter = self::createMock(Exporter::class);
        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        $spans = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            $span = $this->createSampledSpanMock();
            $spans[] = $span;
            /** @var \OpenTelemetry\Sdk\Trace\Span $mock_span */
            $processor->onEnd($span);
        }

        $exporter->expects($this->exactly(1))->method('export')->with($spans);
        $processor->forceFlush();
    }

    /**
     * @test
     */
    public function shutdownCallsExporterShutdown()
    {
        $exporter = self::createMock(Exporter::class);
        $proc = new BatchSpanProcessor($exporter, self::createMock(Clock::class));

        $exporter->expects($this->exactly(1))->method('shutdown');
        $proc->shutdown();
    }

    /**
     * @test
     */
    public function noExportAfterShutdown()
    {
        $exporter = self::createMock(Exporter::class);
        // grap a reference to the InvocationOrder object so we can inspect/assert *when* the call happens
        $exporter->expects($spy = $this->exactly(1))->method('shutdown');

        $this->assertEquals(0, $spy->getInvocationCount());

        $proc = new BatchSpanProcessor($exporter, self::createMock(Clock::class));
        $proc->shutdown();
        // calling SpanProcessor's shutdown() calls Exporter's shutdown()
        $this->assertEquals(1, $spy->getInvocationCount());

        $span = $this->createSampledSpanMock();
        $proc->onStart($span);
        $proc->onEnd($span);

        // calling onEnd here does NOT result in another call to shutdown
        $this->assertEquals(1, $spy->getInvocationCount());
    }

    /**
     * @test
     */
    public function exportsOnlySampledSpans()
    {
        $sampledSpan = $this->createSampledSpanMock();
        $nonSampledSpan = $this->createNonSampledSpanMock();

        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(1))->method('export')->with([$sampledSpan]);

        $batchProcessor = new BatchSpanProcessor($exporter, self::createMock(Clock::class));
        foreach ([$sampledSpan, $nonSampledSpan] as $span) {
            $batchProcessor->onEnd($span);
        }

        $batchProcessor->forceFlush();
    }

    private function createSampledSpanMock()
    {
        return self::createConfiguredMock(Span::class, ['isSampled' => true]);
    }

    private function createNonSampledSpanMock()
    {
        return self::createConfiguredMock(Span::class, ['isSampled' => false]);
    }
}
