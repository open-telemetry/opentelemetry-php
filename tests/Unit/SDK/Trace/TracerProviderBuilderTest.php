<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TracerProviderBuilder::class)]
class TracerProviderBuilderTest extends TestCase
{
    public function test_build_returns_tracer_provider(): void
    {
        $builder = new TracerProviderBuilder();
        $provider = $builder->build();
        $this->assertInstanceOf(TracerProviderInterface::class, $provider);
    }

    public function test_add_span_processor(): void
    {
        $builder = new TracerProviderBuilder();
        $result = $builder->addSpanProcessor($this->createMock(SpanProcessorInterface::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(TracerProviderInterface::class, $builder->build());
    }

    public function test_set_resource(): void
    {
        $builder = new TracerProviderBuilder();
        $result = $builder->setResource($this->createMock(ResourceInfo::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(TracerProviderInterface::class, $builder->build());
    }

    public function test_set_sampler(): void
    {
        $builder = new TracerProviderBuilder();
        $result = $builder->setSampler($this->createMock(SamplerInterface::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(TracerProviderInterface::class, $builder->build());
    }

    public function test_set_configurator(): void
    {
        $builder = new TracerProviderBuilder();
        $result = $builder->setConfigurator(Configurator::tracer());
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(TracerProviderInterface::class, $builder->build());
    }

    public function test_set_span_suppression_strategy(): void
    {
        $builder = new TracerProviderBuilder();
        $result = $builder->setSpanSuppressionStrategy(new NoopSuppressionStrategy());
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(TracerProviderInterface::class, $builder->build());
    }
}
