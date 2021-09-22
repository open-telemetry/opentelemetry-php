<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace\SpanProcessor;

use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Trace\SpanContext;
use PHPUnit\Framework\TestCase;

class BatchSpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExportIfBatchLimitIsReachedButDelayNotReached(): void
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

        // Export will still happen even if clock will never trigger the batch
        $clock = $this->createMock(Clock::class);
        $clock->method('now')->willReturn(($exportDelay - 1));

        /** @var Span[] $spans */
        /** @var Exporter $exporter */
        /** @var Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        foreach ($spans as $span) {
            $processor->onEnd($span);
        }
    }

    /**
     * @test
     */
    public function shouldExportIfDelayLimitReachedButBatchSizeNotReached(): void
    {
        $batchSize = 4;
        $queueSize = 5;
        $exportDelay = 1;
        $timeout = 3000;

        $spans = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            $spans[] = $this->createSampledSpanMock();
        }

        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->once())->method('export')->with($spans);

        // The clock will be "before" the delay until the final call, then the timeout will trigger
        $clock = $this->createMock(Clock::class);

        $timestampReturns = [];
        for ($i = 0; $i < count($spans) - 1; $i++) {
            $timestampReturns[] = ($exportDelay - 1) * 1e6;
        }
        $timestampReturns[count($spans) - 1] = ($exportDelay + 1) * 1e6;

        // forceFlush method will call timestamp once again to set exportedTimestamp
        $timestampReturns[count($spans)] = ($exportDelay - 1) * 1e6;

        $clock
            ->method('now')
            ->willReturnOnConsecutiveCalls(...array_map(static function ($e) {
                return (int) $e;
            }, $timestampReturns));

        /** @var Exporter $exporter */
        /** @var Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        /** @var Span $span */
        foreach ($spans as $span) {
            $processor->onEnd($span);
        }
    }

    /**
     * @test
     */
    public function shouldNotExportIfNotEnoughTimePassedAndBatchNotFull(): void
    {
        $batchSize = 3;
        $queueSize = 5;
        $exportDelay = 2;
        $timeout = 3000;

        $clock = $this->createMock(Clock::class);
        $clock->method('timestamp')->willReturn(($exportDelay - 1));

        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->never())->method('export');

        /** @var Exporter $exporter */
        /** @var Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $mock_span = $this->createSampledSpanMock();
            /** @var Span $mock_span */
            $processor->onEnd($mock_span);
        }
    }

    /**
     * @test
     */
    public function shouldAllowNullExporter(): void
    {
        $proc = new BatchSpanProcessor(null, $this->createMock(Clock::class));
        /** @var Span $span */
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
    public function forceFlushExportsAllEndedSpans(): void
    {
        $batchSize = 3;
        $queueSize = 3;
        $exportDelay = 2;
        $timeout = 3000;

        $clock = $this->createMock(Clock::class);
        $clock->method('timestamp')->willReturn(($exportDelay - 1));

        $exporter = $this->createMock(Exporter::class);
        $processor = new BatchSpanProcessor($exporter, $clock, $queueSize, $exportDelay, $timeout, $batchSize);

        $spans = [];
        for ($i = 0; $i < $batchSize - 1; $i++) {
            /** @var Span $span */
            $span = $this->createSampledSpanMock();
            $spans[] = $span;
            $processor->onEnd($span);
        }

        $exporter->expects($this->once())->method('export')->with($spans);
        $processor->forceFlush();
    }

    /**
     * @test
     */
    public function shutdownCallsExporterShutdown(): void
    {
        $exporter = $this->createMock(Exporter::class);
        $proc = new BatchSpanProcessor($exporter, $this->createMock(Clock::class));

        $exporter->expects($this->once())->method('shutdown');
        $proc->shutdown();
    }

    /**
     * @test
     */
    public function noExportAfterShutdown(): void
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

    /**
     * @test
     */
    public function exportsOnlySampledSpans(): void
    {
        $sampledSpan = $this->createSampledSpanMock();
        $nonSampledSpan = $this->createNonSampledSpanMock();

        $exporter = $this->createMock(Exporter::class);
        $exporter->expects($this->once())->method('export')->with([$sampledSpan]);

        $batchProcessor = new BatchSpanProcessor($exporter, $this->createMock(Clock::class));
        foreach ([$sampledSpan, $nonSampledSpan] as $span) {
            $batchProcessor->onEnd($span);
        }

        $batchProcessor->forceFlush();
    }

    private function createSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(SpanContext::class, ['isSampled' => true]);

        return $this->createConfiguredMock(Span::class, ['getContext' => $spanContext]);
    }

    private function createNonSampledSpanMock()
    {
        $spanContext = $this->createConfiguredMock(SpanContext::class, ['isSampled' => false]);

        return $this->createConfiguredMock(Span::class, ['getContext' => $spanContext]);
    }
}
