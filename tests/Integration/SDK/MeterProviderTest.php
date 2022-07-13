<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Metrics as API;
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
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
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
    public function test_weak_asynchronous_observer_is_released_when_instance_out_of_scope(): void
    {
        $clock = new TestClock();
        $exporter = new CollectingMetricExporter();
        $reader = new ExportingReader($exporter, $clock);
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
            $exporter->collectMetrics(),
        );

        $instance = null;
        $reader->collect();
        $this->assertEquals(
            [
            ],
            $exporter->collectMetrics(),
        );
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

final class CollectingMetricExporter implements MetricExporterInterface
{
    private array $metrics = [];

    /**
     * @var string|Temporality|null
     */
    private $temporality;

    /**
     * @param string|Temporality|null $temporality
     */
    public function __construct($temporality = null)
    {
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function collectMetrics(): array
    {
        $metrics = $this->metrics;
        $this->metrics = [];

        return $metrics;
    }

    public function export(iterable $batch): bool
    {
        foreach ($batch as $metric) {
            $this->metrics[] = $metric;
        }

        return true;
    }

    public function shutdown(): bool
    {
        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }
}
