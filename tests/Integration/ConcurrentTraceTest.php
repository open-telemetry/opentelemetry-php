<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 * This class tests using multiple ContextStorage to allow multiple active traces at the same time.
 */
class ConcurrentTraceTest extends TestCase
{
    public function test_concurrent_traces_are_independent(): void
    {
        $tracerProvider =  new TracerProvider();
        $tracer = $tracerProvider->getTracer();
        $custom = ContextStorage::create('custom');

        //create two active root spans: one in default storage, one in own storage
        $rootOne = $tracer->spanBuilder('root.one')
            ->setStorage($custom)
            ->startSpan();
        $rootOne->activate();
        $rootTwo = $tracer->spanBuilder('root.two')->startSpan();
        $rootTwo->activate();
        $this->assertNotEquals($rootOne->getContext()->getTraceId(), $rootTwo->getContext()->getTraceId(), 'trace ids are different');
        $this->assertNotEquals($rootOne->getContext()->getSpanId(), $rootTwo->getContext()->getSpanId(), 'span ids are different');

        //create a child span in each storage, ensure they parent to the active span from each storage
        $childOne = $tracer->spanBuilder('child.one')->setStorage($custom)->startSpan();
        $childTwo = $tracer->spanBuilder('child.two')->startSpan();
        $this->assertSame($childOne->getContext()->getTraceId(), $rootOne->getContext()->getTraceId());
        $this->assertSame($childTwo->getContext()->getTraceId(), $rootTwo->getContext()->getTraceId());
        $this->assertNotSame($childOne->getContext()->getTraceId(), $childTwo->getContext()->getTraceId());
    }
}
