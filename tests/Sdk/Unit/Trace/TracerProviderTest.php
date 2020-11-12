<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Resource\ResourceConstants;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Sdk\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\Sdk\Trace\Tracer;
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
    /**
     * @test
     */
    public function newTraceProvidersProvidesEmptyResource()
    {
        $traceProvider = new TracerProvider();
        $resource = $traceProvider->getResource();
        $attributes = $resource->getAttributes();

        $this->assertCount(0, $attributes);
    }
    /**
     * @test
     */
    public function newTraceProviderWithTracerProvidesNonEmptyResource()
    {
        $traceProvider = new TracerProvider();
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        $sdkname = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        $sdklanguage = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);
        $servicename = $attributes->getAttribute(ResourceConstants::SERVICE_NAME);
        $serviceversion = $attributes->getAttribute(ResourceConstants::SERVICE_VERSION);

        $this->assertEquals($attributes, $resource->getAttributes());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());
        $this->assertEquals('name', $servicename->getValue());
        $this->assertEquals('version', $serviceversion->getValue());

        $this->assertCount(6, $attributes);
    }
    /**
     * @test
     */
    public function tracerFromTraceProviderAssociatesWithTraceProviderResource()
    {
        /*
         * A resource can be associated with the TracerProvider when the TracerProvider is created.
         * That association cannot be changed later. When associated with a TracerProvider, all
         * Spans produced by any Tracer from the provider MUST be associated with this Resource.
         */

        // Create a new provider with a resource containing 2 attributes.
        $providerResource = ResourceInfo::create(new Attributes(['provider' => 'primary', 'empty' => '']));
        $traceProvider = new TracerProvider($providerResource);
        $tpAttributes = $traceProvider->getResource()->getAttributes();

        // Verify the resource associated with the trace provider.
        $this->assertCount(5, $tpAttributes);
        $primary = $tpAttributes->getAttribute('provider');
        $empty = $tpAttributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        // Add a Tracer.  The trace provider should add its resource to the new Tracer.
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        $sdkname = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        $sdklanguage = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $attributes->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);
        $servicename = $attributes->getAttribute(ResourceConstants::SERVICE_NAME);
        $serviceversion = $attributes->getAttribute(ResourceConstants::SERVICE_VERSION);

        $primary = $attributes->getAttribute('provider');
        $empty = $attributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());
        $this->assertEquals('name', $servicename->getValue());
        $this->assertEquals('version', $serviceversion->getValue());

        $this->assertCount(8, $attributes);
    }
}
