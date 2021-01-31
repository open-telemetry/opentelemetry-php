<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Resource\ResourceConstants;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attribute;
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
    public function gettingTracersWithDifferentVersionReturnsDifferentTracers()
    {
        $traceProvider = new TracerProvider();
        $tracer1 = $traceProvider->getTracer('test_tracer', 'v1');
        $tracer2 = $traceProvider->getTracer('test_tracer', 'v2');

        self::assertNotSame($tracer1, $tracer2);
    }

    /**
     * @test
     */
    public function gettingTracersWithSameNameAndVersionReturnsSameTracer()
    {
        $traceProvider = new TracerProvider();
        $tracer1 = $traceProvider->getTracer('test_tracer', 'v1');
        $tracer2 = $traceProvider->getTracer('test_tracer', 'v1');

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

        $traceProvider = new TracerProvider(null, new ParentBased(new AlwaysOffSampler()));
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
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);
        /** @var Attribute $servicename */
        $servicename = $attributes->getAttribute(ResourceConstants::SERVICE_NAME);
        /** @var Attribute $serviceversion */
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
        /** @var Attribute $primary */
        $primary = $tpAttributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $tpAttributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        // Add a Tracer.  The trace provider should add its resource to the new Tracer.
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);
        /** @var Attribute $servicename */
        $servicename = $attributes->getAttribute(ResourceConstants::SERVICE_NAME);
        /** @var Attribute $serviceversion */
        $serviceversion = $attributes->getAttribute(ResourceConstants::SERVICE_VERSION);

        /** @var Attribute $primary */
        $primary = $attributes->getAttribute('provider');
        /** @var Attribute $empty */
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
