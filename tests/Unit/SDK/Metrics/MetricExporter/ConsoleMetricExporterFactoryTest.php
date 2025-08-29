<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleMetricExporterFactory::class)]
class ConsoleMetricExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new ConsoleMetricExporterFactory())->create();
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }
}
