<?php

namespace OpenTelemetry\Tests\Unit\Trace\SpanProcessor;

use OpenTelemetry\Exporter\ExporterInterface;
use OpenTelemetry\Trace\Span;
use OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessor;
use PHPUnit\Framework\TestCase;

class SimpleSpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallExporterOnEnd()
    {
        $exporter = self::createMock(ExporterInterface::class);
        $exporter->expects($this->atLeastOnce())->method('export');

        (new SimpleSpanProcessor($exporter))->onEnd(
            self::createMock(Span::class)
        );
    }
}