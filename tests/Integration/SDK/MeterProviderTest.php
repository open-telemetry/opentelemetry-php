<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class MeterProviderTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
    }

    public function test_weak_asynchronous_observer_is_released_when_instance_out_of_scope(): void
    {
        $clock = new TestClock();
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $meterProvider = $this->meterProvider($reader, $clock);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $instance = new class($meterProvider) {
            public function __construct(API\MeterProviderInterface $meterProvider)
            {
                $meterProvider
                    ->getMeter('test')
                    ->createObservableUpDownCounter('test')
                    ->observe(fn (ObserverInterface $observer) => $observer->observe($this->count()), true);
            }
            public function count(): int
            {
                return 5;
            }
        };

        $reader->collect();
        $this->assertEquals(
            [
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfoFactory::emptyResource(),
                    'test',
                    null,
                    null,
                    new Sum([
                        new NumberDataPoint(5, Attributes::create([]), TestClock::DEFAULT_START_EPOCH, TestClock::DEFAULT_START_EPOCH),
                    ], Temporality::CUMULATIVE, false),
                ),
            ],
            $exporter->collect(true),
        );

        $instance = null;
        $reader->collect();
        $this->assertEquals(
            [
            ],
            $exporter->collect(true),
        );
    }

    public function test_returns_noop_meter_when_sdk_disabled(): void
    {
        self::setEnvironmentVariable('OTEL_SDK_DISABLED', 'true');
        $clock = new TestClock();
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $meterProvider = $this->meterProvider($reader, $clock);

        $this->assertInstanceOf(NoopMeter::class, $meterProvider->getMeter('test'));
    }

    /**
     * @param MetricReaderInterface&MetricSourceRegistryInterface&DefaultAggregationProviderInterface $metricReader
     */
    private function meterProvider(MetricReaderInterface $metricReader, ClockInterface $clock): MeterProviderInterface
    {
        return new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            $clock,
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$metricReader],
            new CriteriaViewRegistry(),
            new WithSampledTraceExemplarFilter(),
            new ImmediateStalenessHandlerFactory(),
        );
    }
}
