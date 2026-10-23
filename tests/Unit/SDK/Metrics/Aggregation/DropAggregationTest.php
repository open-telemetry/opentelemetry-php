<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Aggregation;

use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Metrics\Aggregation\DropAggregation;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DropAggregation::class)]
#[CoversClass(StreamFactory::class)]
final class DropAggregationTest extends TestCase
{
    private InMemoryExporter $exporter;
    private ExportingReader $reader;
    private CriteriaViewRegistry $views;
    private MeterProvider $meterProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->exporter = new InMemoryExporter();
        $this->reader = new ExportingReader($this->exporter);
        $this->views = new CriteriaViewRegistry();
        $this->meterProvider = new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            new TestClock(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$this->reader],
            $this->views,
            null,
            new ImmediateStalenessHandlerFactory(),
        );
        $this->views->register(
            new InstrumentNameCriteria('dropped'),
            ViewTemplate::create()->withAggregation(new DropAggregation()),
        );
    }

    public function test_drops_synchronous_instrument(): void
    {
        $counter = $this->meterProvider->getMeter('test')->createCounter('dropped');
        $counter->add(1);

        $this->assertFalse($counter->isEnabled());
        $this->assertSame([], $this->exportedNames());
    }

    public function test_drops_asynchronous_instrument(): void
    {
        $counter = $this->meterProvider->getMeter('test')->createObservableCounter('dropped');
        $counter->observe(static fn (ObserverInterface $observer) => $observer->observe(1));

        $this->assertFalse($counter->isEnabled());
        $this->assertSame([], $this->exportedNames());
    }

    public function test_other_view_on_synchronous_instrument_is_kept(): void
    {
        $this->views->register(new InstrumentNameCriteria('dropped'), ViewTemplate::create()->withName('kept'));
        $counter = $this->meterProvider->getMeter('test')->createCounter('dropped');
        $counter->add(1);

        $this->assertTrue($counter->isEnabled());
        $this->assertSame(['kept'], $this->exportedNames());
    }

    public function test_other_view_on_asynchronous_instrument_is_kept(): void
    {
        $this->views->register(new InstrumentNameCriteria('dropped'), ViewTemplate::create()->withName('kept'));
        $counter = $this->meterProvider->getMeter('test')->createObservableCounter('dropped');
        $counter->observe(static fn (ObserverInterface $observer) => $observer->observe(1));

        $this->assertTrue($counter->isEnabled());
        $this->assertSame(['kept'], $this->exportedNames());
    }

    public function test_used_directly_produces_no_data_points(): void
    {
        $aggregation = new DropAggregation();
        $summary = $aggregation->initialize();
        $aggregation->record($summary, 5, Attributes::create([]), Context::getCurrent(), 1);

        $data = $aggregation->toData(
            [Attributes::create([])],
            [$aggregation->merge($summary, $aggregation->diff($summary, $summary))],
            [],
            0,
            1,
            Temporality::CUMULATIVE,
        );

        $this->assertSame([], $data->dataPoints);
    }

    /**
     * @return array<string>
     */
    private function exportedNames(): array
    {
        $this->reader->collect();

        return array_map(static fn (Metric $metric): string => $metric->name, $this->exporter->collect());
    }
}
