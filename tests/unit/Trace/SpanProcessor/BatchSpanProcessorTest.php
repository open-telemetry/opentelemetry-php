<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Trace\SpanProcessor;

use OpenTelemetry\Sdk\Internal\Clock;
use OpenTelemetry\Sdk\Internal\Time;
use OpenTelemetry\Sdk\Internal\Timestamp;
use OpenTelemetry\Sdk\Trace\BatchSpanProcessor;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
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
        $clock->method('now')->will($this->returnValue(Timestamp::at(($exportDelay + 1) * Time::MILLISECOND)));

        $exporter->expects($this->atLeastOnce())->method('export');

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
        $clock->method('now')->will($this->returnValue(Timestamp::at(($exportDelay + 1) * Time::MILLISECOND)));

        $processor = new BatchSpanProcessor($exporter, $clock, $batchSize, $exportDelay, 3000, $batchSize);

        for ($i = 0; $i < $batchSize - 1; $i++) {
            $processor->onEnd(self::createMock(Span::class));
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
        $clock->method('now')->will($this->returnValue(Timestamp::at(($exportDelay - 1) * Time::MILLISECOND)));

        $processor = new BatchSpanProcessor($exporter, $clock, $batchSize, $exportDelay, 3000, $batchSize);

        for ($i = 0; $i < $batchSize; $i++) {
            $processor->onEnd(self::createMock(Span::class));
        }
    }
}
