<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterProvider::class)]
final class MeterProviderTest extends TestCase
{
    public function test_get_meter_creates_instrumentation_scope_with_given_arguments(): void
    {
        $instrumentationScopeFactory = $this->createMock(InstrumentationScopeFactoryInterface::class);
        $instrumentationScopeFactory->expects($this->once())->method('create')
            ->with(
                'name',
                '0.0.1',
                'https://schema-url.test',
                [],
            )
            ->willReturn(new InstrumentationScope('name', '0.0.1', 'https://schema-url.test', Attributes::create([])));

        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            Clock::getDefault(),
            Attributes::factory(),
            $instrumentationScopeFactory,
            [],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $meterProvider->getMeter('name', '0.0.1', 'https://schema-url.test');
    }

    public function test_get_meter_returns_noop_meter_after_shutdown(): void
    {
        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            Clock::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $meterProvider->shutdown();

        $this->assertInstanceOf(NoopMeter::class, $meterProvider->getMeter('name'));
    }

    public function test_shutdown_calls_metric_reader_shutdown(): void
    {
        $metricReader = $this->createMock(MetricReaderSourceRegistryInterface::class);
        $metricReader->expects($this->once())->method('shutdown')->willReturn(true);

        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            Clock::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$metricReader],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $this->assertTrue($meterProvider->shutdown());
    }

    public function test_force_flush_calls_metric_reader_force_flush(): void
    {
        $metricReader = $this->createMock(MetricReaderSourceRegistryInterface::class);
        $metricReader->expects($this->once())->method('forceFlush')->willReturn(true);

        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            Clock::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$metricReader],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $this->assertTrue($meterProvider->forceFlush());
    }

    public function test_disable(): void
    {
        $meterProvider = MeterProvider::builder()->addReader(new ExportingReader(new InMemoryExporter()))->build();
        $this->assertInstanceOf(MeterProvider::class, $meterProvider);
        $meter = $meterProvider->getMeter('one');
        $this->assertTrue($meter->isEnabled());
        $counter = $meter->createCounter('A');
        $this->assertTrue($counter->enabled());
        $meterProvider->updateConfigurator(Configurator::builder()->addCondition(new Name('~one~'), State::DISABLED)->build());
        $this->assertFalse($meter->isEnabled());
        $this->assertFalse($counter->enabled());
    }
}

interface MetricReaderSourceRegistryInterface extends MetricReaderInterface, MetricSourceRegistryInterface, DefaultAggregationProviderInterface
{
}
