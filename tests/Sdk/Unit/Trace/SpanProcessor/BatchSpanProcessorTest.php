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
    public function shouldExportIfBatchLimitIsReached()
    {
        $batchSize = 3;
        $exportDelay = 1;
        $spans = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $spans[] = self::createMock(Span::class);
        }

        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->at(0))->method('export')->with($spans);

        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay + 1)));

        $exporter->expects($this->atLeastOnce())->method('export');

        /** @var \OpenTelemetry\Sdk\Trace\Span[] $spans */
        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $batchSize, $exportDelay, 3000, $batchSize);

        foreach ($spans as $span) {
            $processor->onEnd($span);
        }
    }

    /**
     * @test
     */
    public function shouldNotExportIfSpanBatchDidntReachedLimit()
    {
        $batchSize = 3;
        $exportDelay = 1;
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(0))->method('export');
        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay + 1)));

        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $batchSize, $exportDelay, 3000, $batchSize);

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $mock_span = self::createMock(Span::class);
            /** @var \OpenTelemetry\Sdk\Trace\Span $mock_span */
            $processor->onEnd($mock_span);
        }
    }

    /**
     * @test
     */
    public function shouldNotExportIfNotEnoughTimePassedSinceLastExport()
    {
        $batchSize = 3;
        $exporter = self::createMock(Exporter::class);
        $exporter->expects($this->exactly(0))->method('export');

        $exportDelay = 2;
        $clock = self::createMock(Clock::class);
        $clock->method('timestamp')->will($this->returnValue(($exportDelay - 1)));

        /** @var \OpenTelemetry\Sdk\Trace\Exporter $exporter */
        /** @var \OpenTelemetry\Sdk\Trace\Clock $clock */
        $processor = new BatchSpanProcessor($exporter, $clock, $batchSize, $exportDelay, 3000, $batchSize);

        for ($i = 0; $i < $batchSize; $i++) {
            $mock_span = self::createMock(Span::class);
            /** @var \OpenTelemetry\Sdk\Trace\Span $mock_span */
            $processor->onEnd($mock_span);
        }
    }
}
