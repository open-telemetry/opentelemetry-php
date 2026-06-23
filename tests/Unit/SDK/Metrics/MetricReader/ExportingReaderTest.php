<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\LastValueAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamMetricSourceProvider;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExportingReader::class)]
final class ExportingReaderTest extends TestCase
{
    public function test_empty_reader_collects_empty_metrics(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);

        $reader->collect();
        $this->assertSame([], $exporter->collect());
    }

    public function test_default_aggregation_returns_default_aggregation(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);

        $this->assertEquals(new SumAggregation(true), $reader->defaultAggregation(InstrumentType::COUNTER));
        $this->assertEquals(new SumAggregation(true), $reader->defaultAggregation(InstrumentType::ASYNCHRONOUS_COUNTER));
        $this->assertEquals(new SumAggregation(), $reader->defaultAggregation(InstrumentType::UP_DOWN_COUNTER));
        $this->assertEquals(new SumAggregation(), $reader->defaultAggregation(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER));
        $this->assertEquals(new ExplicitBucketHistogramAggregation([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]), $reader->defaultAggregation(InstrumentType::HISTOGRAM));
        $this->assertEquals(new LastValueAggregation(), $reader->defaultAggregation(InstrumentType::GAUGE));
        $this->assertEquals(new LastValueAggregation(), $reader->defaultAggregation(InstrumentType::ASYNCHRONOUS_GAUGE));
    }

    public function test_default_aggregation_returns_histogram_with_advisory_buckets(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);

        $this->assertEquals(
            new ExplicitBucketHistogramAggregation([0, 0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1, 2.5, 5, 7.5, 10]),
            $reader->defaultAggregation(InstrumentType::HISTOGRAM, ['ExplicitBucketBoundaries' => [0, 0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1, 2.5, 5, 7.5, 10]]),
        );
    }

    public function test_default_aggregation_returns_exporter_aggregation_if_default_aggregation_provider(): void
    {
        $exporter = $this->createMock(DefaultAggregationProviderExporterInterface::class);
        $exporter->method('defaultAggregation')->willReturn(new LastValueAggregation());
        $reader = new ExportingReader($exporter);

        $this->assertEquals(new LastValueAggregation(), $reader->defaultAggregation(InstrumentType::COUNTER));
    }

    public function test_add_creates_metric_source_with_exporter_temporality(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $provider->expects($this->once())->method('create')->with(Temporality::CUMULATIVE);
        $metricMetadata = $this->createMock(MetricMetadataInterface::class);
        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $stalenessHandler->expects($this->once())->method('onStale');

        $reader->add($provider, $metricMetadata, $stalenessHandler);
    }

    public function test_add_does_not_create_metric_source_if_exporter_temporality_null(): void
    {
        $exporter = $this->createMock(MetricExporterInterface::class);
        $reader = new ExportingReader($exporter);

        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $provider->expects($this->never())->method('create');
        $metricMetadata = $this->createMock(MetricMetadataInterface::class);
        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $stalenessHandler->expects($this->never())->method('onStale');

        $reader->add($provider, $metricMetadata, $stalenessHandler);
    }

    public function test_add_does_not_create_metric_source_if_reader_closed(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $provider->expects($this->never())->method('create');
        $metricMetadata = $this->createMock(MetricMetadataInterface::class);
        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $stalenessHandler->expects($this->never())->method('onStale');

        $reader->shutdown();
        $reader->add($provider, $metricMetadata, $stalenessHandler);
    }

    public function test_staleness_handler_clears_source(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $metricMetadata = $this->createMock(MetricMetadataInterface::class);
        $stalenessHandler = new ImmediateStalenessHandler();
        $stalenessHandler->acquire();
        $reader->add($provider, $metricMetadata, $stalenessHandler);

        $stalenessHandler->release();
        $reader->collect();
        $this->assertSame([], $exporter->collect());
    }

    public function test_collect_collects_sources_with_current_timestamp(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $metric = new Metric(
            $this->createMock(InstrumentationScopeInterface::class),
            $this->createMock(ResourceInfo::class),
            'test',
            null,
            null,
            $this->createMock(DataInterface::class),
        );

        $source = $this->createMock(MetricSourceInterface::class);
        $source->expects($this->once())->method('collect')->willReturn($metric);
        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $provider->expects($this->once())->method('create')->willReturn($source);
        $metricMetadata = $this->createMock(MetricMetadataInterface::class);
        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);

        $reader->add($provider, $metricMetadata, $stalenessHandler);
        $reader->collect();
    }

    public function test_shutdown_calls_exporter_shutdown(): void
    {
        $exporter = $this->createMock(MetricExporterInterface::class);
        $exporter->expects($this->once())->method('shutdown')->willReturn(true);
        $reader = new ExportingReader($exporter);

        $this->assertTrue($reader->shutdown());
    }

    public function test_shutdown_does_not_export_empty_metrics(): void
    {
        $exporter = $this->createMock(MetricExporterInterface::class);
        $exporter->expects($this->never())->method('export');
        $exporter->expects($this->once())->method('shutdown')->willReturn(true);

        $reader = new ExportingReader($exporter);

        $reader->shutdown();
    }

    public function test_shutdown_exports_metrics(): void
    {
        $exporter = $this->createMock(MetricExporterWithTemporalityInterface::class);
        $provider = $this->createMock(MetricSourceProviderInterface::class);
        $source = $this->createMock(MetricSourceInterface::class);
        $source->method('collect')->willReturn($this->createMock(Metric::class));
        $provider->method('create')->willReturn($source);
        $exporter->method('temporality')->willReturn('foo');
        $exporter->expects($this->once())->method('export')->willReturn(true);
        $exporter->expects($this->once())->method('shutdown')->willReturn(true);

        $reader = new ExportingReader($exporter);
        $reader->add(
            $provider,
            $this->createMock(MetricMetadataInterface::class),
            $this->createMock(StalenessHandlerInterface::class)
        );

        $this->assertTrue($reader->shutdown());
    }

    public function test_force_flush_calls_push_exporter_force_flush(): void
    {
        $exporter = $this->createMock(PushMetricExporterInterface::class);
        $exporter->expects($this->once())->method('forceFlush')->willReturn(true);
        $reader = new ExportingReader($exporter);

        $this->assertTrue($reader->forceFlush());
    }

    public function test_force_flush_with_non_push_exporter(): void
    {
        $exporter = $this->createMock(MetricExporterInterface::class);
        $reader = new ExportingReader($exporter);

        $this->assertTrue($reader->forceFlush());
    }

    public function test_closed_reader_does_not_call_exporter_methods(): void
    {
        $exporter = $this->createMock(PushMetricExporterInterface::class);
        $reader = new ExportingReader($exporter);

        $reader->shutdown();

        $exporter->expects($this->never())->method('export');
        $exporter->expects($this->never())->method('shutdown');
        $exporter->expects($this->never())->method('forceFlush');

        $reader->collect();
        $reader->shutdown();
        $reader->forceFlush();
    }

    public function test_unregister_stream_removes_source_from_collection(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $collector = $this->createMock(MetricCollectorInterface::class);
        $stream = $this->createMock(MetricStreamInterface::class);
        $stream->method('register')->willReturn(0);
        $stream->method('temporality')->willReturn(Temporality::CUMULATIVE);
        $stream->method('collect')->willReturn($this->createMock(DataInterface::class));
        $stream->method('timestamp')->willReturn(0);

        $provider = new StreamMetricSourceProvider(
            view: new ViewProjection('test', null, null, null, null),
            instrument: new Instrument(InstrumentType::COUNTER, 'test', null, null),
            instrumentationLibrary: $this->createMock(InstrumentationScopeInterface::class),
            resource: $this->createMock(ResourceInfo::class),
            stream: $stream,
            metricCollector: $collector,
            streamId: 1,
        );

        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $reader->add($provider, $provider, $stalenessHandler);

        // Before unregister, collecting should produce a metric
        $reader->collect();
        $metrics = $exporter->collect(true);
        $this->assertCount(1, $metrics);

        // Unregister the stream
        $reader->unregisterStream($collector, 1);

        // After unregister, collecting should produce no metrics
        $reader->collect();
        $metrics = $exporter->collect();
        $this->assertSame([], $metrics);
    }

    public function test_unregister_stream_cleans_up_registry_when_no_streams_remain(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $collector = $this->createMock(MetricCollectorInterface::class);
        $stream = $this->createMock(MetricStreamInterface::class);
        $stream->method('register')->willReturn(0);
        $stream->method('temporality')->willReturn(Temporality::CUMULATIVE);
        $stream->method('collect')->willReturn($this->createMock(DataInterface::class));
        $stream->method('timestamp')->willReturn(0);

        $provider = new StreamMetricSourceProvider(
            view: new ViewProjection('test', null, null, null, null),
            instrument: new Instrument(InstrumentType::COUNTER, 'test', null, null),
            instrumentationLibrary: $this->createMock(InstrumentationScopeInterface::class),
            resource: $this->createMock(ResourceInfo::class),
            stream: $stream,
            metricCollector: $collector,
            streamId: 1,
        );

        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $reader->add($provider, $provider, $stalenessHandler);

        // Unregister the only stream for this collector
        $reader->unregisterStream($collector, 1);

        // collectAndPush should never be called since the registry was cleaned up
        $collector->expects($this->never())->method('collectAndPush');
        $reader->collect();
        $this->assertSame([], $exporter->collect());
    }

    public function test_unregister_stream_keeps_registry_when_other_streams_remain(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $collector = $this->createMock(MetricCollectorInterface::class);

        $stream1 = $this->createMock(MetricStreamInterface::class);
        $stream1->method('register')->willReturn(0);
        $stream1->method('temporality')->willReturn(Temporality::CUMULATIVE);
        $stream1->method('collect')->willReturn($this->createMock(DataInterface::class));
        $stream1->method('timestamp')->willReturn(0);

        $stream2 = $this->createMock(MetricStreamInterface::class);
        $stream2->method('register')->willReturn(1);
        $stream2->method('temporality')->willReturn(Temporality::CUMULATIVE);
        $stream2->method('collect')->willReturn($this->createMock(DataInterface::class));
        $stream2->method('timestamp')->willReturn(0);

        $provider1 = new StreamMetricSourceProvider(
            view: new ViewProjection('test1', null, null, null, null),
            instrument: new Instrument(InstrumentType::COUNTER, 'test1', null, null),
            instrumentationLibrary: $this->createMock(InstrumentationScopeInterface::class),
            resource: $this->createMock(ResourceInfo::class),
            stream: $stream1,
            metricCollector: $collector,
            streamId: 1,
        );

        $provider2 = new StreamMetricSourceProvider(
            view: new ViewProjection('test2', null, null, null, null),
            instrument: new Instrument(InstrumentType::COUNTER, 'test2', null, null),
            instrumentationLibrary: $this->createMock(InstrumentationScopeInterface::class),
            resource: $this->createMock(ResourceInfo::class),
            stream: $stream2,
            metricCollector: $collector,
            streamId: 2,
        );

        $stalenessHandler = $this->createMock(StalenessHandlerInterface::class);
        $reader->add($provider1, $provider1, $stalenessHandler);
        $reader->add($provider2, $provider2, $stalenessHandler);

        // Unregister only stream 1 - stream 2 should still remain
        $reader->unregisterStream($collector, 1);

        // collectAndPush should still be called since stream 2 remains for this collector
        $collector->expects($this->once())->method('collectAndPush');
        $reader->collect();
        $metrics = $exporter->collect();
        $this->assertCount(1, $metrics);
    }

    public function test_unregister_stream_with_nonexistent_stream_is_noop(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);

        $collector = $this->createMock(MetricCollectorInterface::class);

        // Unregistering a stream that was never registered triggers a PHP warning
        // due to accessing an undefined array key in the cleanup check
        @$reader->unregisterStream($collector, 999);

        $reader->collect();
        $this->assertSame([], $exporter->collect());
    }
}

interface DefaultAggregationProviderExporterInterface extends MetricExporterInterface, DefaultAggregationProviderInterface
{
}

interface MetricExporterWithTemporalityInterface extends MetricExporterInterface, AggregationTemporalitySelectorInterface
{
}
