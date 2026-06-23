<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleMetricExporter::class)]
class ConsoleMetricExporterTest extends TestCase
{
    public function test_export_returns_true(): void
    {
        $exporter = new ConsoleMetricExporter();

        $metric = $this->createMetric();

        ob_start();
        $result = $exporter->export([$metric]);
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function test_export_outputs_json(): void
    {
        $exporter = new ConsoleMetricExporter();

        $metric = $this->createMetric('test_metric', 'ms', 'A test metric');

        ob_start();
        $exporter->export([$metric]);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('resource', $decoded);
        $this->assertArrayHasKey('scope', $decoded);
        $this->assertArrayHasKey('metrics', $decoded['scope']);
        $this->assertCount(1, $decoded['scope']['metrics']);
        $this->assertSame('test_metric', $decoded['scope']['metrics'][0]['name']);
        $this->assertSame('ms', $decoded['scope']['metrics'][0]['unit']);
        $this->assertSame('A test metric', $decoded['scope']['metrics'][0]['description']);
    }

    public function test_export_with_multiple_metrics(): void
    {
        $exporter = new ConsoleMetricExporter();

        $metric1 = $this->createMetric('metric_one');
        $metric2 = $this->createMetric('metric_two');

        ob_start();
        $exporter->export([$metric1, $metric2]);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertCount(2, $decoded['scope']['metrics']);
        $this->assertSame('metric_one', $decoded['scope']['metrics'][0]['name']);
        $this->assertSame('metric_two', $decoded['scope']['metrics'][1]['name']);
    }

    public function test_export_with_empty_batch(): void
    {
        $exporter = new ConsoleMetricExporter();

        ob_start();
        $result = $exporter->export([]);
        $output = ob_get_clean();

        $this->assertTrue($result);
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertNull($decoded['resource']);
        $this->assertNull($decoded['scope']);
    }

    public function test_shutdown_returns_true(): void
    {
        $exporter = new ConsoleMetricExporter();
        $this->assertTrue($exporter->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $exporter = new ConsoleMetricExporter();
        $this->assertTrue($exporter->forceFlush());
    }

    public function test_temporality_returns_configured_temporality(): void
    {
        $exporter = new ConsoleMetricExporter(Temporality::DELTA);

        $metric = $this->createMock(MetricMetadataInterface::class);
        $metric->method('temporality')->willReturn(Temporality::CUMULATIVE);

        $this->assertSame(Temporality::DELTA, $exporter->temporality($metric));
    }

    public function test_temporality_returns_metric_temporality_when_not_configured(): void
    {
        $exporter = new ConsoleMetricExporter();

        $metric = $this->createMock(MetricMetadataInterface::class);
        $metric->method('temporality')->willReturn(Temporality::CUMULATIVE);

        $this->assertSame(Temporality::CUMULATIVE, $exporter->temporality($metric));
    }

    private function createMetric(
        string $name = 'test',
        ?string $unit = null,
        ?string $description = null,
    ): Metric {
        $attributes = $this->createMock(AttributesInterface::class);
        $attributes->method('toArray')->willReturn(['service.name' => 'test-service']);
        $attributes->method('getDroppedAttributesCount')->willReturn(0);

        $resource = $this->createMock(ResourceInfo::class);
        $resource->method('getAttributes')->willReturn($attributes);

        $scopeAttributes = $this->createMock(AttributesInterface::class);
        $scopeAttributes->method('toArray')->willReturn([]);
        $scopeAttributes->method('getDroppedAttributesCount')->willReturn(0);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('test-scope');
        $scope->method('getVersion')->willReturn('1.0.0');
        $scope->method('getAttributes')->willReturn($scopeAttributes);
        $scope->method('getSchemaUrl')->willReturn(null);

        $data = $this->createMock(DataInterface::class);

        return new Metric($scope, $resource, $name, $unit, $description, $data);
    }
}
