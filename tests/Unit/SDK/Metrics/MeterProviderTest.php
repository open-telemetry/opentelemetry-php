<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MeterProvider
 */
final class MeterProviderTest extends TestCase
{
    use ProphecyTrait;

    public function test_get_meter_creates_instrumentation_scope_with_given_arguments(): void
    {
        $instrumentationScopeFactory = $this->prophesize(InstrumentationScopeFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $instrumentationScopeFactory
            ->create()
            ->shouldBeCalledOnce()
            ->withArguments([
                'name',
                '0.0.1',
                'https://schema-url.test',
                [],
            ])
            ->willReturn(new InstrumentationScope('name', '0.0.1', 'https://schema-url.test', Attributes::create([])));

        /** @noinspection PhpParamsInspection */
        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            Attributes::factory(),
            $instrumentationScopeFactory->reveal(),
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
            ClockFactory::getDefault(),
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
        /** @psalm-suppress TooFewArguments */
        $metricReader = $this->prophesize()
            ->willImplement(MetricSourceRegistryInterface::class)
            ->willImplement(MetricReaderInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricReader
            ->shutdown()
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            /** @phpstan-ignore-next-line */
            [$metricReader->reveal()],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $this->assertTrue($meterProvider->shutdown());
    }

    public function test_force_flush_calls_metric_reader_force_flush(): void
    {
        /** @psalm-suppress TooFewArguments */
        $metricReader = $this->prophesize()
            ->willImplement(MetricSourceRegistryInterface::class)
            ->willImplement(MetricReaderInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $metricReader
            ->forceFlush()
            ->shouldBeCalledOnce()
            ->willReturn(true);

        /** @noinspection PhpParamsInspection */
        $meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            /** @phpstan-ignore-next-line */
            [$metricReader->reveal()],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $this->assertTrue($meterProvider->forceFlush());
    }
}
