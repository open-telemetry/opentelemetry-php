<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter
 * @psalm-suppress UndefinedInterfaceMethod
 */
class InMemoryExporterTest extends TestCase
{
    public function test_export(): void
    {
        $exporter = new InMemoryExporter();
        $batch = [
            (new ReadableLogRecord(
                $this->createMock(InstrumentationScopeInterface::class),
                $this->createMock(LoggerSharedState::class),
                (new LogRecord('foo')),
            )),
        ];

        $this->assertTrue($exporter->export($batch)->await());
    }

    public function test_force_flush(): void
    {
        $exporter = new InMemoryExporter();
        $this->assertTrue($exporter->forceFlush());
    }

    public function test_shutdown(): void
    {
        $exporter = new InMemoryExporter();
        $this->assertTrue($exporter->shutdown());
    }

    public function test_storage(): void
    {
        $exporter = new InMemoryExporter();
        $this->assertCount(0, $exporter->getStorage());
    }
}
