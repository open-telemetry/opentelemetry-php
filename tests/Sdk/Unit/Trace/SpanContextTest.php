<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Tests\Sdk\Unit\Support\HasTraceProvider;
use OpenTelemetry\Sdk\Trace\Span;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    use HasTraceProvider;

    /**
     * @test
     */
    public function testDefaultSpansFromContextAreNotSampled()
    {
        $span = SpanContext::generate();

        $this->assertFalse($span->isSampled());
    }

    /**
     * @test
     */
    public function testSpanContextCanCreateSampledSpans()
    {
        $span = SpanContext::generate(true);

        $this->assertTrue($span->isSampled());

        $span = SpanContext::generateSampled();

        $this->assertTrue($span->isSampled());
    }

    /**
     * @test
     */
    public function testDefaultSpansFromTracerAreSampled()
    {
        $tracer = $this->getTracer();

        $span = $tracer->startAndActivateSpan('test');

        $this->assertTrue($span->isSampled());
    }

    /**
     * @test
     */
    public function testSpansFromTracerInheritParentIsSampledStatus()
    {
        $tracer = $this->getTracer();

        $context = SpanContext::generate(true);

        $activeSpan = new Span('test.span', $context);

        $tracer->setActiveSpan($activeSpan);

        $span = $tracer->startAndActivateSpan('test');

        $this->assertTrue($span->isSampled());
    }
}
