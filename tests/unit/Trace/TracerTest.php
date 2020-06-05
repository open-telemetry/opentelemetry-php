<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenNewSpanIsCreated()
    {
        $processor = self::createMock(SpanProcessor::class);
        $processor->expects($this->exactly(1))->method('onStart');

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($processor);

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $tracer->startAndActivateSpan('test.span');
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenActiveSpanIsEnded()
    {
        $processor = self::createMock(SpanProcessor::class);

        $processor->expects($this->exactly(1))->method('onEnd');

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($processor);

        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $tracer->startAndActivateSpan('test.span');
        $tracer->endActiveSpan();
    }
}
