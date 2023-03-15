<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory
 */
class ConsoleExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new ConsoleExporterFactory();

        $this->assertInstanceOf(ConsoleExporter::class, $factory->create());
    }
}
