<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

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

    public function test_type(): void
    {
        $factory = new NoopMetricExporterFactory();
        $this->assertSame('none', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new NoopMetricExporterFactory();
        $this->assertSame(0, $factory->priority());
    }
}
