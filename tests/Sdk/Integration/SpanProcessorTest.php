<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace\Span;
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

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->startSpan('test.span', $parentContext);
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

        $tracerProvider = new TracerProvider();
        $tracerProvider->addSpanProcessor($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->startSpan('test.span', null);
    }
}
