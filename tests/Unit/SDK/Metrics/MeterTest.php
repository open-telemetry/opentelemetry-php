<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use function func_get_arg;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Metrics\ViewRegistryInterface;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Meter
 */
final class MeterTest extends TestCase
{
    public function test_create_counter(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name', 'unit', 'description');
    }

    public function test_create_histogram(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::HISTOGRAM, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createHistogram('name', 'unit', 'description');
    }

    public function test_create_histogram_advisory(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(
                    InstrumentType::HISTOGRAM,
                    'http.server.duration',
                    's',
                    'Measures the duration of inbound HTTP requests.',
                    ['ExplicitBucketBoundaries' => [0, 0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1, 2.5, 5, 7.5, 10]],
                ),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createHistogram(
            'http.server.duration',
            's',
            'Measures the duration of inbound HTTP requests.',
            ['ExplicitBucketBoundaries' => [0, 0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1, 2.5, 5, 7.5, 10]],
        );
    }

    public function test_create_up_down_counter(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::UP_DOWN_COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createUpDownCounter('name', 'unit', 'description');
    }

    public function test_create_observable_counter(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createAsynchronousObserver')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableCounter('name', 'unit', 'description');
    }

    public function test_create_observable_gauge(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createAsynchronousObserver')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_GAUGE, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableGauge('name', 'unit', 'description');
    }

    public function test_create_observable_up_down_counter(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createAsynchronousObserver')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableUpDownCounter('name', 'unit', 'description');
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function test_reuses_writer_when_not_stale(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $counter = $meter->createCounter('name', 'unit', 'description');
        $counter = $meter->createCounter('name', 'unit', 'description');
    }

    public function test_releases_writer_on_stale(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(2))->method('createSynchronousWriter')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name', 'unit', 'description');
        $meter->createCounter('name', 'unit', 'description');
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function test_reuses_observer_when_not_stale(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->once())->method('createAsynchronousObserver')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $observer = $meter->createObservableCounter('name', 'unit', 'description');
        $observer = $meter->createObservableCounter('name', 'unit', 'description');
    }

    public function test_releases_observer_on_stale(): void
    {
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(2))->method('createAsynchronousObserver')
            ->with(
                $this->anything(),
                $this->anything(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                $this->anything(),
                $this->anything(),
            )
            ->willReturn([]);

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableCounter('name', 'unit', 'description');
        $meter->createObservableCounter('name', 'unit', 'description');
    }

    /**
     * @psalm-suppress InvalidOperand
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public function test_uses_view_registry_to_create_views(): void
    {
        $aggregation = $this->createMock(AggregationInterface::class);

        $metricReader = $this->createMock(MeterMetricReaderInterface::class);

        $viewRegistry = $this->createMock(ViewRegistryInterface::class);
        $viewRegistry->method('find')->willReturn([
            new ViewProjection('view-1', null, null, null, $aggregation),
            new ViewProjection('view-2', null, null, null, $aggregation),
        ]);

        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(1))->method('createSynchronousWriter')
            ->willReturnCallback(function () use ($aggregation): array {
                [[$v1], [$v2]] = [...func_get_arg(5)];

                $this->assertEquals(new ViewProjection('view-1', null, null, null, $aggregation), $v1);
                $this->assertEquals(new ViewProjection('view-2', null, null, null, $aggregation), $v2);

                return [];
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory, $viewRegistry, [$metricReader]);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name');
    }

    /**
     * @psalm-suppress InvalidOperand
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public function test_uses_default_aggregation_if_view_aggregation_null(): void
    {
        $aggregation = $this->createMock(AggregationInterface::class);

        $metricReader = $this->createMock(MeterMetricReaderInterface::class);
        $metricReader->method('defaultAggregation')->willReturn($aggregation);

        $viewRegistry = $this->createMock(ViewRegistryInterface::class);
        $viewRegistry->method('find')->willReturn([
            new ViewProjection('view-1', null, null, null, null),
        ]);

        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(1))->method('createSynchronousWriter')
            ->willReturnCallback(function () use ($aggregation): array {
                [[$v1]] = [...func_get_arg(5)];

                $this->assertEquals(new ViewProjection('view-1', null, null, null, $aggregation), $v1);

                return [];
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory, $viewRegistry, [$metricReader]);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name');
    }

    /**
     * @psalm-suppress InvalidOperand
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public function test_uses_default_view_if_null_views_returned(): void
    {
        $aggregation = $this->createMock(AggregationInterface::class);

        $metricReader = $this->createMock(MeterMetricReaderInterface::class);
        $metricReader->method('defaultAggregation')->willReturn($aggregation);

        $viewRegistry = $this->createMock(ViewRegistryInterface::class);
        $viewRegistry->method('find')->willReturn(null);

        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(1))->method('createSynchronousWriter')
            ->willReturnCallback(function () use ($aggregation): array {
                [[$v1]] = [...func_get_arg(5)];

                $this->assertEquals(new ViewProjection('name', null, null, null, $aggregation), $v1);

                return [];
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory, $viewRegistry, [$metricReader]);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name');
    }

    /**
     * @param iterable<MetricReaderInterface&MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricReaders
     */
    private function createMeterProviderForMetricFactory(MetricFactoryInterface $metricFactory, ViewRegistryInterface $viewRegistry = null, iterable $metricReaders = []): MeterProvider
    {
        return new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            $metricReaders,
            $viewRegistry ?? new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
            $metricFactory,
        );
    }
}

interface MeterMetricReaderInterface extends MetricReaderInterface, MetricSourceRegistryInterface, DefaultAggregationProviderInterface
{
}
