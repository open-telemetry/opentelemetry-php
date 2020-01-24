<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Trace;

use OpenTelemetry\Trace\SpanProcessor\SpanProcessorInterface;
use OpenTelemetry\Trace\Tracer;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenNewSpanIsCreated()
    {
        $processor = self::createMock(SpanProcessorInterface::class);
        $processor->expects($this->atLeastOnce())->method('onStart');

        $tracer = new Tracer([$processor]);

        $tracer->createSpan('test.span');
    }

    /**
     * @test
     */
    public function spanProcessorsShouldBeCalledWhenActiveSpanIsEnded()
    {
        $processor = self::createMock(SpanProcessorInterface::class);

        $processor->expects($this->atLeastOnce())->method('onEnd');

        $tracer = new Tracer([$processor]);

        $tracer->createSpan('test.span');
        $tracer->endActiveSpan();
    }
}
