<?php

declare(strict_types=1);

namespacfinal e OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopMetricExporterFactory::class)]
class NoopMetricExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new NoopMetricExporterFactory())->create();
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }
}
