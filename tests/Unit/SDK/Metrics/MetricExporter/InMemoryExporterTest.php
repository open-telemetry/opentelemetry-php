<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(InMemoryExporter::class)]
final class InMemoryExporterTest extends TestCase
{
    public function test_empty_exporter_collect_returns_empty_metrics(): void
    {
        $exporter = new InMemoryExporter();

        $this->assertSame([], $exporter->collect());
    }

    public function test_exporter_collect_returns_exported_metrics(): void
    {
        $exporter = new InMemoryExporter();
        $metrics = $this->generateMetrics(2);

        $exporter->export($metrics);
        $this->assertSame($metrics, $exporter->collect());
    }

    public function test_exporter_collect_retains_exported_metrics(): void
    {
        $exporter = new InMemoryExporter();
        $metrics = $this->generateMetrics(2);

        $exporter->export($metrics);
        $this->assertSame($metrics, $exporter->collect());
        $this->assertSame($metrics, $exporter->collect());
    }

    public function test_exporter_collect_reset_resets_exported_metrics(): void
    {
        $exporter = new InMemoryExporter();
        $metrics = $this->generateMetrics(2);

        $exporter->export($metrics);
        $this->assertSame($metrics, $exporter->collect(true));
        $this->assertSame([], $exporter->collect());
    }

    public function test_exporter_collect_returns_all_exported_metrics(): void
    {
        $exporter = new InMemoryExporter();
        $metrics = $this->generateMetrics(4);

        $exporter->export([$metrics[0], $metrics[1]]);
        $exporter->export([$metrics[2], $metrics[3]]);
        $this->assertSame($metrics, $exporter->collect());
    }

    public function test_temporality_returns_configured_temporality(): void
    {
        $exporter = new InMemoryExporter(temporality: Temporality::DELTA);
        $metric = $this->createMock(MetricMetadataInterface::class);
        $metric->expects($this->never())->method('temporality');

        $this->assertSame(Temporality::DELTA, $exporter->temporality($metric));
    }

    public function test_temporality_delegates_to_metric_when_not_configured(): void
    {
        $exporter = new InMemoryExporter();
        $metric = $this->createMock(MetricMetadataInterface::class);
        $metric->expects($this->once())->method('temporality')->willReturn(Temporality::CUMULATIVE);

        $this->assertSame(Temporality::CUMULATIVE, $exporter->temporality($metric));
    }

    public function test_shutdown_returns_true(): void
    {
        $exporter = new InMemoryExporter();
        $this->assertTrue($exporter->shutdown());
    }

    public function test_shutdown_returns_false_when_already_closed(): void
    {
        $exporter = new InMemoryExporter();
        $exporter->shutdown();
        $this->assertFalse($exporter->shutdown());
    }

    public function test_export_returns_false_when_closed(): void
    {
        $exporter = new InMemoryExporter();
        $exporter->shutdown();
        $this->assertFalse($exporter->export([]));
    }

    public function test_force_flush_returns_true(): void
    {
        $exporter = new InMemoryExporter();
        $this->assertTrue($exporter->forceFlush());
    }

    /**
     * @return list<Metric>
     */
    private function generateMetrics(int $count): array
    {
        $metrics = [];
        for ($i = 0; $i < $count; $i++) {
            $metrics[] = new Metric(
                $this->createMock(InstrumentationScopeInterface::class),
                $this->createMock(ResourceInfo::class),
                sprintf('test-%d', $i),
                null,
                null,
                $this->createMock(DataInterface::class),
            );
        }

        return $metrics;
    }
}
