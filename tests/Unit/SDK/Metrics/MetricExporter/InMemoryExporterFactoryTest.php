<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory
 */
class InMemoryExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new InMemoryExporterFactory())->create();
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }
}
