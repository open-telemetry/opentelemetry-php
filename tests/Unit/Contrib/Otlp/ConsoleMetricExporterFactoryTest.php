<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\ConsoleMetricExporterFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\ConsoleMetricExporterFactory
 */
class ConsoleMetricExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new ConsoleMetricExporterFactory())->create();
        $this->assertInstanceOf(MetricExporter::class, $exporter);
    }
}
