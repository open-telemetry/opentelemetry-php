<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\SpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class SpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function parentContextShouldBePassedToSpanProcessor()
    {
        $parentContext = new Context();

        $spanProcessor = $this->createMock(SpanProcessor::class);
        $spanProcessor
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(Span::class), $this->equalTo($parentContext))
        ;

        $tracerProvider = new TracerProvider($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->spanBuilder('test.span')->setParent($parentContext)->startSpan();
    }

    /**
     * @test
     */
    public function currentContextShouldBePassedToSpanProcessorByDefault()
    {
        $currentContext = Context::getCurrent();

        $spanProcessor = $this->createMock(SpanProcessor::class);
        $spanProcessor
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(Span::class), $this->equalTo($currentContext))
        ;

        $tracerProvider = new TracerProvider($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->spanBuilder('test.span')->startSpan();
    }
}
