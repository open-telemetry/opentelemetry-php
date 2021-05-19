<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Baggage;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Tests\Sdk\Unit\Support\HasTraceProvider;
use PHPUnit\Framework\TestCase;

class BaggageTest extends TestCase
{
    use HasTraceProvider;

    /**
     * @test
     */
    public function testDefaultSpansFromContextAreNotSampled()
    {
        $span = Baggage::generate();

        $this->assertFalse($span->isSampled());
    }

    /**
     * @test
     */
    public function testBaggageCanCreateSampledSpans()
    {
        $span = Baggage::generate(true);

        $this->assertTrue($span->isSampled());

        $span = Baggage::generateSampled();

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

        $context = Baggage::generate(true);

        $activeSpan = new Span('test.span', $context);

        $tracer->setActiveSpan($activeSpan);

        $span = $tracer->startAndActivateSpan('test');

        $this->assertTrue($span->isSampled());
    }
    /**
     * @test
     */
    public function testSampledSpansFromTracerDoNotInheritParentIsRemoteStatus()
    {
        // When creating children from remote spans, their IsRemote flag MUST be set to false.
        $tracer = $this->getTracer();

        $context = Baggage::generate(true);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context, true);

        $this->assertTrue($remoteSpan->isRemote());

        $span = $tracer->startAndActivateSpan('test');

        $this->assertFalse($span->isRemote());
    }
    /**
     * @test
     */
    public function testNotSampledSpansFromTracerDoNotInheritParentIsRemoteStatus()
    {
        // When creating children from remote spans, their IsRemote flag MUST be set to false.
        $tracer = $this->getTracer();

        $context = Baggage::generate(false);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context, true);

        $this->assertTrue($remoteSpan->isRemote());

        $span = $tracer->startAndActivateSpan('test');

        $this->assertFalse($span->isRemote());
    }
    /**
     * @test
     */
    public function testIsRemoteStatus()
    {
        /*
         * Test that when creating a sampled span from remote context, the
         * `isRemote` flag is set to true.
         */
        $tracer = $this->getTracer();

        $context1 = Baggage::generate(true);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context1, true);

        $this->assertTrue($remoteSpan->isRemote());
        /*
         * Test that when creating an unsampled span from remote context, the
         * `isRemote` flag is set to true.
         */
        $tracer = $this->getTracer();

        $context2 = Baggage::generate(false);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context2, true);

        $this->assertTrue($remoteSpan->isRemote());
        /*
         * Test that when creating a sampled span from
         * a non-remote context, the
         * `isRemote` flag is set to false.
         */
        $tracer = $this->getTracer();

        $context3 = Baggage::generate(true);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context3);

        $this->assertFalse($remoteSpan->isRemote());
        /*
         * Test that when creating an unsampled span from
         * a non-remote context, the
         * `isRemote` flag is set to false.
         */
        $tracer = $this->getTracer();

        $context2 = Baggage::generate(false);

        $remoteSpan = $tracer->startAndActivateSpanFromContext('test.span', $context2);

        $this->assertFalse($remoteSpan->isRemote());
    }
}
