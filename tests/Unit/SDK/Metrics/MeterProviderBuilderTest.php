<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterProviderBuilder::class)]
class MeterProviderBuilderTest extends TestCase
{
    public function test_build_returns_meter_provider(): void
    {
        $builder = new MeterProviderBuilder();
        $provider = $builder->build();
        $this->assertInstanceOf(MeterProviderInterface::class, $provider);
    }

    public function test_set_resource(): void
    {
        $builder = new MeterProviderBuilder();
        $result = $builder->setResource($this->createMock(ResourceInfo::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(MeterProviderInterface::class, $builder->build());
    }

    public function test_set_exemplar_filter(): void
    {
        $builder = new MeterProviderBuilder();
        $result = $builder->setExemplarFilter($this->createMock(ExemplarFilterInterface::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(MeterProviderInterface::class, $builder->build());
    }

    public function test_add_reader(): void
    {
        $builder = new MeterProviderBuilder();
        $result = $builder->addReader($this->createMock(MetricReaderInterface::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(MeterProviderInterface::class, $builder->build());
    }

    public function test_set_configurator(): void
    {
        $builder = new MeterProviderBuilder();
        $result = $builder->setConfigurator(Configurator::meter());
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(MeterProviderInterface::class, $builder->build());
    }

    public function test_set_clock(): void
    {
        $builder = new MeterProviderBuilder();
        $result = $builder->setClock($this->createMock(ClockInterface::class));
        $this->assertSame($builder, $result);
        $this->assertInstanceOf(MeterProviderInterface::class, $builder->build());
    }
}
