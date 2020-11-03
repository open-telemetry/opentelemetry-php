<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Sdk\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerProviderTest extends TestCase
{

    /**
     * @test
     */
    public function gettingSameTracerMultipleTimesShouldReturnSameObject()
    {
        $traceProvider = new TracerProvider();
        $tracer1 = $traceProvider->getTracer('test_tracer');
        $tracer2 = $traceProvider->getTracer('test_tracer');

        self::assertSame($tracer1, $tracer2);
    }

    /**
     * @test
     */
    public function newTraceProviderDefaultsToAlwaysOnSampler()
    {
        $traceProvider = new TracerProvider();
        $description = $traceProvider->getSampler()->getDescription();

        self::assertSame($description, 'AlwaysOnSampler');
    }

    /**
     * @test
     */
    public function newTraceProviderAcceptsOtherSamplers()
    {
        $traceProvider = new TracerProvider(null, new AlwaysOffSampler());
        $description = $traceProvider->getSampler()->getDescription();

        self::assertSame($description, 'AlwaysOffSampler');

        $traceProvider = new TracerProvider(null, new ParentBased());
        $description = $traceProvider->getSampler()->getDescription();

        self::assertSame($description, 'ParentBased');

        $traceProvider = new TracerProvider(null, new AlwaysOnSampler());
        $description = $traceProvider->getSampler()->getDescription();

        self::assertSame($description, 'AlwaysOnSampler');

        $traceProvider = new TracerProvider(null, new TraceIdRatioBasedSampler(0.5));
        $description = $traceProvider->getSampler()->getDescription();

        self::assertSame($description, 'TraceIdRatioBasedSampler{0.500000}');
    }
}
