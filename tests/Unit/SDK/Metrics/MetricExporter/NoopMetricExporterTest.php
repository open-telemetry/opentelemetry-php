<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMetricExporter::class)]
class NoopMetricExporterTest extends TestCase
{
    public function test_export_returns_true(): void
    {
        $exporter = new NoopMetricExporter();
        $this->assertTrue($exporter->export([]));
    }

    public function test_shutdown_returns_true(): void
    {
        $exporter = new NoopMetricExporter();
        $this->assertTrue($exporter->shutdown());
    }
}
