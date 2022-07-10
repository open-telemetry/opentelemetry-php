<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\CompositeReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
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

    private function createMeterProviderForMetricFactory(MetricFactoryInterface $metricFactory): MeterProvider
    {
        /** @noinspection PhpParamsInspection */
        return new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            new InstrumentationScopeFactory(Attributes::factory()),
            new CompositeReader([]),
            Attributes::factory(),
            new ImmediateStalenessHandlerFactory(),
            $metricFactory,
        );
    }
}
