<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use function func_get_arg;
use OpenTelemetry\API\Metrics\ObserverInterface;
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
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Metrics\ViewRegistryInterface;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Meter
 */
final class MeterTest extends TestCase
{
    use ProphecyTrait;

    public function test_create_counter(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createSynchronousWriter()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name', 'unit', 'description');
    }

    public function test_create_histogram(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createSynchronousWriter()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::HISTOGRAM, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createHistogram('name', 'unit', 'description');
    }

    public function test_create_up_down_counter(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createSynchronousWriter()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::UP_DOWN_COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createUpDownCounter('name', 'unit', 'description');
    }

    public function test_create_observable_counter(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createAsynchronousObserver()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableCounter('name', 'unit', 'description');
    }

    public function test_create_observable_gauge(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createAsynchronousObserver()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_GAUGE, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableGauge('name', 'unit', 'description');
    }

    public function test_create_observable_up_down_counter(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createAsynchronousObserver()
            ->shouldBeCalledOnce()
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableUpDownCounter('name', 'unit', 'description');
    }

    public function test_create_observable_counter_register_permanent_callback(): void
    {
        $callable = fn (ObserverInterface $observer) => $observer->observe(0);
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->method('createAsynchronousObserver')
            ->willReturnCallback(function () use ($callable) {
                $observer = $this->createMock(MetricObserverInterface::class);
                $observer->expects($this->once())->method('observe')->with($callable);

                return $observer;
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableCounter('name', 'unit', 'description', $callable);
    }

    public function test_create_observable_gauge_register_permanent_callback(): void
    {
        $callable = fn (ObserverInterface $observer) => $observer->observe(0);
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->method('createAsynchronousObserver')
            ->willReturnCallback(function () use ($callable) {
                $observer = $this->createMock(MetricObserverInterface::class);
                $observer->expects($this->once())->method('observe')->with($callable);

                return $observer;
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableGauge('name', 'unit', 'description', $callable);
    }

    public function test_create_observable_up_down_counter_register_permanent_callback(): void
    {
        $callable = fn (ObserverInterface $observer) => $observer->observe(0);
        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->method('createAsynchronousObserver')
            ->willReturnCallback(function () use ($callable) {
                $observer = $this->createMock(MetricObserverInterface::class);
                $observer->expects($this->once())->method('observe')->with($callable);

                return $observer;
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory);
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableUpDownCounter('name', 'unit', 'description', $callable);
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function test_reuses_writer_when_not_stale(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createSynchronousWriter()
            ->shouldBeCalledTimes(1)
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $counter = $meter->createCounter('name', 'unit', 'description');
        $counter = $meter->createCounter('name', 'unit', 'description');
    }

    public function test_releases_writer_on_stale(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createSynchronousWriter()
            ->shouldBeCalledTimes(2)
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name', 'unit', 'description');
        $meter->createCounter('name', 'unit', 'description');
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function test_reuses_observer_when_not_stale(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createAsynchronousObserver()
            ->shouldBeCalledTimes(1)
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $observer = $meter->createObservableCounter('name', 'unit', 'description');
        $observer = $meter->createObservableCounter('name', 'unit', 'description');
    }

    public function test_releases_observer_on_stale(): void
    {
        $metricFactory = $this->prophesize(MetricFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricFactory
            ->createAsynchronousObserver()
            ->shouldBeCalledTimes(2)
            ->withArguments([
                ResourceInfoFactory::emptyResource(),
                new InstrumentationScope('test', null, null, Attributes::create([])),
                new Instrument(InstrumentType::ASYNCHRONOUS_COUNTER, 'name', 'unit', 'description'),
                Argument::cetera(),
            ]);

        /** @noinspection PhpParamsInspection */
        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory->reveal());
        $meter = $meterProvider->getMeter('test');
        $meter->createObservableCounter('name', 'unit', 'description');
        $meter->createObservableCounter('name', 'unit', 'description');
    }

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
            ->willReturnCallback(function () use ($aggregation): MetricWriterInterface {
                [[$v1], [$v2]] = [...func_get_arg(4)];

                $this->assertEquals(new ViewProjection('view-1', null, null, null, $aggregation), $v1);
                $this->assertEquals(new ViewProjection('view-2', null, null, null, $aggregation), $v2);

                return $this->createMock(MetricWriterInterface::class);
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory, $viewRegistry, [$metricReader]);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name');
    }

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
            ->willReturnCallback(function () use ($aggregation): MetricWriterInterface {
                [[$v1]] = [...func_get_arg(4)];

                $this->assertEquals(new ViewProjection('view-1', null, null, null, $aggregation), $v1);

                return $this->createMock(MetricWriterInterface::class);
            });

        $meterProvider = $this->createMeterProviderForMetricFactory($metricFactory, $viewRegistry, [$metricReader]);
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('name');
    }

    public function test_uses_default_view_if_null_views_returned(): void
    {
        $aggregation = $this->createMock(AggregationInterface::class);

        $metricReader = $this->createMock(MeterMetricReaderInterface::class);
        $metricReader->method('defaultAggregation')->willReturn($aggregation);

        $viewRegistry = $this->createMock(ViewRegistryInterface::class);
        $viewRegistry->method('find')->willReturn(null);

        $metricFactory = $this->createMock(MetricFactoryInterface::class);
        $metricFactory->expects($this->exactly(1))->method('createSynchronousWriter')
            ->willReturnCallback(function () use ($aggregation): MetricWriterInterface {
                [[$v1]] = [...func_get_arg(4)];

                $this->assertEquals(new ViewProjection('name', null, null, null, $aggregation), $v1);

                return $this->createMock(MetricWriterInterface::class);
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
