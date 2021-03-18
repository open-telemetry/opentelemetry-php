<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Trace;

use OpenTelemetry\Sdk\Trace\NoopSpan;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace\SpanContext;
use PHPUnit\Framework\TestCase;

class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function noopSpanShouldBeStartedWhenSamplingResultIsDrop()
    {
        $alwaysOffSampler = new AlwaysOffSampler();
        $tracerProvider = new TracerProvider(null, $alwaysOffSampler);
        $processor = self::createMock(SpanProcessor::class);
        $tracerProvider->addSpanProcessor($processor);
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracerTest');

        $processor->expects($this->never())->method('onStart');
        $span = $tracer->startSpan('test.span');

        $this->assertInstanceOf(NoopSpan::class, $span);
        $this->assertNotEquals(SpanContext::TRACE_FLAG_SAMPLED, $span->getContext()->getTraceFlags());
    }
}
