<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\Tracer;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenNewSpanIsCreated()
    {
        $processor = self::createMock(SpanProcessor::class);
        $processor->expects($this->atLeastOnce())->method('onStart');

        $tracer = new Tracer([$processor]);

        $tracer->startAndActivateSpan('test.span');
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenActiveSpanIsEnded()
    {
        $processor = self::createMock(SpanProcessor::class);

        $processor->expects($this->atLeastOnce())->method('onEnd');

        $tracer = new Tracer([$processor]);

        $tracer->startAndActivateSpan('test.span');
        $tracer->endActiveSpan();
    }
}
