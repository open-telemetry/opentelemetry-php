<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Integration;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class SpanProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function test_parent_context_should_be_passed_to_span_processor(): void
    {
        $parentContext = new Context();

        $spanProcessor = $this->createMock(SpanProcessorInterface::class);
        $spanProcessor
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(SpanInterface::class), $this->equalTo($parentContext))
        ;

        $tracerProvider = new TracerProvider($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->spanBuilder('test.span')->setParent($parentContext)->startSpan();
    }

    /**
     * @test
     */
    public function test_current_context_should_be_passed_to_span_processor_by_default(): void
    {
        $currentContext = Context::getCurrent();

        $spanProcessor = $this->createMock(SpanProcessorInterface::class);
        $spanProcessor
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(SpanInterface::class), $this->equalTo($currentContext))
        ;

        $tracerProvider = new TracerProvider($spanProcessor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.Test');
        $tracer->spanBuilder('test.span')->startSpan();
    }
}
