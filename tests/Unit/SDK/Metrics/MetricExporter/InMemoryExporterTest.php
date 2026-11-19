<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\TemporalityPreference;
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    public function test_temporality_preference_selects_by_instrument_type(): void
    {
        $exporter = new InMemoryExporter(temporality: TemporalityPreference::delta());
        $counter = $this->createStub(MetricMetadataInterface::class);
        $counter->method('instrumentType')->willReturn(InstrumentType::COUNTER);
        $upDownCounter = $this->createStub(MetricMetadataInterface::class);
        $upDownCounter->method('instrumentType')->willReturn(InstrumentType::UP_DOWN_COUNTER);

        $this->assertSame(Temporality::DELTA, $exporter->temporality($counter));
        $this->assertSame(Temporality::CUMULATIVE, $exporter->temporality($upDownCounter));
    }

    public function test_without_temporality_uses_the_stream_temporality(): void
    {
        $exporter = new InMemoryExporter();
        $metric = $this->createStub(MetricMetadataInterface::class);
        $metric->method('temporality')->willReturn(Temporality::DELTA);

        $this->assertSame(Temporality::DELTA, $exporter->temporality($metric));
    }

    /**
     * @return list<Metric>
     */
    private function generateMetrics(int $count): array
    {
        $metrics = [];
        for ($i = 0; $i < $count; $i++) {
            $metrics[] = new Metric(
                $this->createStub(InstrumentationScopeInterface::class),
                $this->createStub(ResourceInfo::class),
                sprintf('test-%d', $i),
                null,
                null,
                $this->createStub(DataInterface::class),
            );
        }

        return $metrics;
    }
}
